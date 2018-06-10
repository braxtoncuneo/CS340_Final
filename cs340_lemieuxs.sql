-- phpMyAdmin SQL Dump
-- version 4.8.1
-- https://www.phpmyadmin.net/
--
-- Host: classmysql.engr.oregonstate.edu:3306
-- Generation Time: Jun 09, 2018 at 09:15 PM
-- Server version: 10.1.22-MariaDB
-- PHP Version: 7.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cs340_lemieuxs`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`cs340_lemieuxs`@`%` PROCEDURE `addItem` (IN `world` INT, IN `name` VARCHAR(255), IN `descText` TEXT, IN `location` VARCHAR(255))  MODIFIES SQL DATA
BEGIN

DECLARE state INT;

SET state = (SELECT sID from default_state WHERE wID = world);

INSERT INTO item (itemName, description, wID)
VALUES (name, descText, world);

INSERT INTO item_location(wID, placeName, itemName, sID)
VALUES (world,location,name,state);

END$$

CREATE DEFINER=`cs340_lemieuxs`@`%` PROCEDURE `deleteItem` (IN `iName` VARCHAR(255), IN `worldNo` INT)  MODIFIES SQL DATA
BEGIN

DELETE FROM item
WHERE itemName = iName AND wID = worldNo;

END$$

CREATE DEFINER=`cs340_lemieuxs`@`%` PROCEDURE `deletePlace` (IN `pName` VARCHAR(255), IN `worldNo` INT)  MODIFIES SQL DATA
BEGIN

DELETE FROM place
WHERE placeName = pName AND wID = worldNo;

END$$

CREATE DEFINER=`cs340_lemieuxs`@`%` PROCEDURE `deleteWorld` (IN `confirm` VARCHAR(255), IN `worldNo` INT)  MODIFIES SQL DATA
BEGIN

IF TRIM(confirm) = 'YES'
THEN
	SIGNAL SQLSTATE '45000'
    	SET MESSAGE_TEXT = 'You must enter \'YES\' to confirm world deletion.';
END IF;

DELETE FROM world
WHERE wID = worldNo;

END$$

CREATE DEFINER=`cs340_lemieuxs`@`%` PROCEDURE `dropItem` (IN `itemN` VARCHAR(255), IN `save` INT, IN `worldID` INT)  MODIFIES SQL DATA
BEGIN

UPDATE item_location
SET placeName = (
	SELECT placeName
    FROM save_state S
    WHERE S.sID = save
)
WHERE sID = save AND wID = worldID AND itemName = itemN;

END$$

CREATE DEFINER=`cs340_lemieuxs`@`%` PROCEDURE `goVia` (IN `path` VARCHAR(255), IN `save` INT, IN `worldID` INT)  NO SQL
BEGIN

DECLARE curr_location VARCHAR(255);
DECLARE req_item VARCHAR(255);
SET curr_location = (SELECT placeName
	FROM save_state S
	WHERE S.sID = save
);

/* check if path exists */
IF EXISTS (SELECT 1
    FROM path P1
    WHERE P1.wID = worldID AND P1.fromPlace = curr_location AND P1.pathName = path) THEN

/* Check item requirements */
	IF EXISTS (SELECT 1
    FROM path_req P3
    WHERE P3.wID = worldID AND P3.pathName = path) THEN
    
        SET req_item = (SELECT P4.reqName
        FROM path_req P4
        WHERE P4.wID = worldID AND P4.pathName = path
        );
        
        /* Check if player's inventory contains the required item */
        IF EXISTS (SELECT 1
        FROM item_location I1
        WHERE I1.wID = worldID AND I1.sID = save AND I1.placeName = 'inventory' AND itemName = req_item) THEN
        	/* Take the path */
            UPDATE save_state
            SET placeName = 
            (SELECT P2.toPlace
            FROM path P2
            WHERE P2.wID = worldID AND P2.fromPlace = curr_location AND P2.pathName = path)
            WHERE sID = save AND wID = worldID;
        END IF;
    
    ELSE
    	/* Take the path */
        UPDATE save_state
        SET placeName = 
        (SELECT P2.toPlace
        FROM path P2
        WHERE P2.wID = worldID AND P2.fromPlace = curr_location AND P2.pathName = path)
        WHERE sID = save AND wID = worldID;
    END IF;
END IF;

END$$

CREATE DEFINER=`cs340_lemieuxs`@`%` PROCEDURE `listPlayers` ()  READS SQL DATA
BEGIN
SELECT * FROM player;
END$$

CREATE DEFINER=`cs340_lemieuxs`@`%` PROCEDURE `lookAt` (IN `thing` VARCHAR(255), IN `save` INT, IN `worldID` INT)  NO SQL
BEGIN
    SELECT DISTINCT item.itemName, item_location.placeName, item_req.reqName
    FROM item INNER JOIN item_location
    ON (item.itemName = item_location.itemName AND item.wID = item_location.wID) LEFT JOIN item_req ON (item.itemName = item_req.itemName AND item.wID = item_req.wID)
    WHERE item.wID = worldID AND item.itemName = item;

END$$

CREATE DEFINER=`cs340_lemieuxs`@`%` PROCEDURE `makeItem` (IN `iName` VARCHAR(255), IN `iLocation` VARCHAR(255), IN `iDesc` TEXT, IN `iReq` VARCHAR(255), IN `sText` TEXT, IN `fText` TEXT, IN `worldNo` INT)  MODIFIES SQL DATA
BEGIN


IF NOT EXISTS (
    SELECT placeName
    FROM place
    WHERE wID = worldNo AND placeName = iLocation
)
THEN
	SIGNAL SQLSTATE '45000'
    	SET MESSAGE_TEXT = 'Entered location does not exist in this world.';
END IF;


INSERT INTO item (itemName, description, wID)
VALUES (iName,iDesc,worldNo);

INSERT INTO item_location (wID, placeName, itemName,sID)
VALUES (worldNo, iLocation, iName, (
    SELECT sID
    FROM default_state
    WHERE wID = worldNo
    )
);

IF iReq = NULL
THEN
INSERT INTO item_req (itemName, reqName, wID, success_text, failure_text)
VALUES (iName,iReq,worldNo,sText,fText);
END IF;

END$$

CREATE DEFINER=`cs340_lemieuxs`@`%` PROCEDURE `makeMessage` (IN `sender` TEXT, IN `reciever` TEXT, IN `body` TEXT, IN `secretNumber` INT)  MODIFIES SQL DATA
BEGIN

CREATE TEMPORARY TABLE ret (id INT AUTO_INCREMENT, logText TEXT, PRIMARY KEY (id));


INSERT INTO message (`sender`,`reciever`,`body`,`secretNumber`)
VALUES (sender,reciever,body,secretNumber);


INSERT INTO ret (logText) VALUES (CONCAT(sender," tells ",reciever," : ",body));

SELECT * FROM ret;
END$$

CREATE DEFINER=`cs340_lemieuxs`@`%` PROCEDURE `makePlace` (IN `pName` VARCHAR(255), IN `descText` TEXT, IN `worldNo` INT)  NO SQL
BEGIN

INSERT INTO place (placeName, description, wID)
VALUES (pName,descText,worldNo);

END$$

CREATE DEFINER=`cs340_lemieuxs`@`%` PROCEDURE `makeWorld` (IN `wName` VARCHAR(255), IN `uName` VARCHAR(255))  MODIFIES SQL DATA
BEGIN

INSERT INTO world (worldName, private, owner)
VALUES (wName,true,uName);

END$$

CREATE DEFINER=`cs340_lemieuxs`@`%` PROCEDURE `pickUp` (IN `item` VARCHAR(255), IN `save` INT, IN `worldID` INT)  NO SQL
BEGIN

DECLARE curr_location VARCHAR(255);
DECLARE req_item VARCHAR(255);
SET curr_location = (SELECT placeName
	FROM save_state S
	WHERE S.sID = save
);

/* Check if item is in the current room */
IF EXISTS (SELECT 1
    FROM item_location I
    WHERE I.sID = save AND I.wID = worldID AND I.itemName = item AND I.placeName = curr_location) THEN
    
    /* Check if there is a required item */
	IF EXISTS (SELECT 1
    FROM item_req I2
    WHERE I2.wID = worldID AND I2.itemName = item) THEN
    
        SET req_item = (SELECT I3.reqName
        FROM item_req I3
        WHERE I3.wID = worldID AND I3.itemName = item
        );
        
        /* Check if player's inventory contains the required item */
        IF EXISTS (SELECT 1
        FROM item_location I1
        WHERE I1.wID = worldID AND I1.sID = save AND I1.placeName = 'inventory' AND itemName = req_item) THEN
        	/* add to inventory */
            UPDATE item_location
            SET placeName = 'inventory'
            WHERE itemName = item AND sID = save and wID = worldID;
        END IF;
 	ELSE
    	/* add to inventory */
        UPDATE item_location
        SET placeName = 'inventory'
        WHERE itemName = item AND sID = save and wID = worldID;
    
    END IF;
    
END IF;


END$$

CREATE DEFINER=`cs340_lemieuxs`@`%` PROCEDURE `selectWorld` (IN `worldNo` INT, IN `uName` VARCHAR(255))  MODIFIES SQL DATA
BEGIN

IF worldNo IN (
    SELECT world.wID
    FROM world
    WHERE world.owner = uName
)
THEN
	SELECT world.wID
    FROM world
    WHERE FALSE = TRUE;
ELSE
	SIGNAL SQLSTATE '45000'
    	SET MESSAGE_TEXT = 'Selected world number does not correspond to any of your worlds';
END IF;

END$$

CREATE DEFINER=`cs340_lemieuxs`@`%` PROCEDURE `setupStateForPlayer` (IN `user` VARCHAR(255), IN `worldID` INT)  MODIFIES SQL DATA
BEGIN

DECLARE done INT default 0;
DECLARE theState INT default 0;
DECLARE startPlace VARCHAR(255);
DECLARE c_item VARCHAR(255);
DECLARE c_place VARCHAR(255);


DECLARE itemCurse CURSOR FOR
(SELECT itemName, placeName FROM item_location
	WHERE	wID = worldID AND
    		sID IN (SELECT sID FROM default_state)
);

DECLARE CONTINUE HANDLER FOR NOT found SET done = 1;



SET startPlace = (SELECT placeName FROM save_state
					WHERE	wID = worldID AND
        					sID IN (SELECT sID FROM default_state)
                );

INSERT INTO save_state ( wID, placeName )
	VALUES ( worldID, startPlace );

SET theState = LAST_INSERT_ID();


INSERT INTO player_state( sID, username )
	VALUES (theState,`user`);

OPEN itemCurse;
REPEAT
    FETCH itemCurse INTO c_item, c_place;

	IF !done THEN

    INSERT INTO item_location (wID, placeName,itemName,sID)
    	VALUES (worldID,c_place,c_item,theState);

	END IF;

UNTIL done
END REPEAT;

CLOSE itemCurse;

END$$

CREATE DEFINER=`cs340_lemieuxs`@`%` PROCEDURE `showInventory` (IN `save` INT, IN `world` INT)  NO SQL
BEGIN
	SELECT I.itemName AS INVENTORY 
    FROM item_location I
    WHERE I.sID = save AND I.wID = world;
END$$

CREATE DEFINER=`cs340_lemieuxs`@`%` PROCEDURE `showLocation` (IN `save` INT, IN `world` INT)  NO SQL
BEGIN
	SELECT S.placeName AS 'CURRENT ROOM' 
    FROM save_state S
    WHERE S.sID = save AND S.wID = world;
END$$

CREATE DEFINER=`cs340_lemieuxs`@`%` PROCEDURE `signIn` (IN `uName` VARCHAR(40), IN `passHash` VARCHAR(40))  MODIFIES SQL DATA
BEGIN

IF passHash = (
    SELECT player.password_hash
    FROM player
    WHERE player.username = uName
)
THEN
	SELECT player.username
    FROM player
    WHERE FALSE = TRUE;
ELSE
	SIGNAL SQLSTATE '45000'
    	SET MESSAGE_TEXT = 'Invalid username/password combination.';
END IF;

END$$

CREATE DEFINER=`cs340_lemieuxs`@`%` PROCEDURE `signupPlayer` (IN `uName` VARCHAR(40), IN `passHash` VARCHAR(40), IN `saltVal` VARCHAR(8))  MODIFIES SQL DATA
BEGIN
INSERT INTO player (username, password_hash, salt)
VALUES (uName,passHash,saltVal);
END$$

CREATE DEFINER=`cs340_lemieuxs`@`%` PROCEDURE `viewItems` (IN `worldNo` INT(255))  NO SQL
BEGIN

SELECT I.itemName AS NAME, IL.placeName as LOCATION, I.description AS DESCRIPTION, IF(ISNULL(IR.reqName),'NO REQUIREMENT',IR.reqName) AS REQUIREMENT, IR.success_text as SUCCESS_TEXT, IR.failure_text AS FAILURE_TEXT
FROM item AS I
INNER JOIN item_location AS IL ON IL.wID = I.wID AND IL.itemName = I.itemName
LEFT JOIN item_req AS IR ON IR.wID = I.wID AND IR.itemName = I.itemName
WHERE I.wID = worldNo AND IL.sID = (
 	SELECT sID
    FROM default_state
    WHERE wID = worldNo
);

END$$

CREATE DEFINER=`cs340_lemieuxs`@`%` PROCEDURE `viewPlaces` (IN `worldNo` INT)  MODIFIES SQL DATA
BEGIN

SELECT P.placeName AS NAME, P.description AS DESCRIPTION
FROM place AS P
WHERE P.wID = worldNo;

END$$

CREATE DEFINER=`cs340_lemieuxs`@`%` PROCEDURE `viewWorlds` (IN `uName` VARCHAR(255))  MODIFIES SQL DATA
BEGIN

SELECT W.wID AS ID, W.worldName AS NAME, IF(W.private=1,'TRUE','FALSE') AS PRIVATE, IF(ISNULL(AVG(R.rating)),'NOT RATED',AVG(R.rating)) AS RATING 
FROM world AS W
LEFT JOIN world_rating AS R ON R.wID = W.wID
WHERE W.owner = uName
GROUP BY W.wID;

END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `default_state`
--

CREATE TABLE `default_state` (
  `sID` int(11) NOT NULL,
  `wID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `default_state`
--

INSERT INTO `default_state` (`sID`, `wID`) VALUES
(1, 1),
(2, 2),
(3, 3),
(4, 4),
(5, 5),
(12, 7),
(13, 8),
(14, 9),
(15, 10),
(16, 11),
(17, 12),
(18, 13),
(19, 14),
(26, 21);

-- --------------------------------------------------------

--
-- Table structure for table `item`
--

CREATE TABLE `item` (
  `itemName` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `wID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `item`
--

INSERT INTO `item` (`itemName`, `description`, `wID`) VALUES
('bear trap', 'Yet another sign you should leave this cave.', 1),
('boat', 'The boat lies broken, marooned on the sand.', 1),
('item1', 'the first item', 21),
('item2', '2nd item', 21),
('key', 'A worn key.', 1),
('ladder', 'A metal step ladder. Old, but sturdy.', 1),
('mushroom', 'It is quite large and probably poisonous.', 1),
('music box', 'It is ornately carved.', 13),
('paddle', 'Half rotten away. Must have been for the boat.', 1),
('rock', 'It is a rock.', 4),
('rope', 'A spool of rope.', 1),
('skeleton', 'The skull has a large bite mark on it.', 1),
('Spooky Skull', 'It chatters softly.', 13),
('swimming trunks', 'A nice swimming suit.', 1),
('tea', 'You refuse to leave the room before drinking it.', 1),
('telescope', 'The telescope looks out onto the horizon.', 3),
('test object', 'Replace with actual item later.', 2),
('Test Object', 'This is a test', 14),
('Toy Car', 'It is so old, most of the paint has peeled off.', 13);

--
-- Triggers `item`
--
DELIMITER $$
CREATE TRIGGER `delete_item` BEFORE DELETE ON `item` FOR EACH ROW BEGIN
DELETE FROM save_state
	WHERE 	wID = OLD.wID AND
			sID NOT IN (SELECT sID FROM default_state);
DELETE FROM item_location WHERE
	itemName = OLD.itemName AND
	sID IN (SELECT sID FROM default_state);
DELETE FROM item_req
	WHERE wID = OLD.wID AND
    (itemName = OLD.itemName OR reqName = OLD.itemName);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `item_location`
--

CREATE TABLE `item_location` (
  `wID` int(11) NOT NULL,
  `placeName` varchar(255) NOT NULL,
  `itemName` varchar(255) NOT NULL,
  `sID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `item_location`
--

INSERT INTO `item_location` (`wID`, `placeName`, `itemName`, `sID`) VALUES
(4, 'a rose patch', 'rock', 4),
(4, 'a rose patch', 'rock', 9),
(4, 'a rose patch', 'rock', 11),
(1, 'beach', 'boat', 1),
(1, 'beach', 'boat', 6),
(1, 'beach', 'boat', 8),
(1, 'beach', 'boat', 10),
(1, 'beach', 'rope', 1),
(1, 'beach', 'rope', 6),
(1, 'beach', 'rope', 8),
(1, 'beach', 'rope', 10),
(13, 'Black Lodge Entranceway', 'Spooky Skull', 18),
(1, 'cave', 'bear trap', 1),
(1, 'cave', 'bear trap', 6),
(1, 'cave', 'bear trap', 8),
(1, 'cave', 'bear trap', 10),
(1, 'cave', 'key', 1),
(1, 'cave', 'key', 6),
(1, 'cave', 'key', 8),
(1, 'cave', 'key', 10),
(1, 'cave', 'ladder', 1),
(1, 'cave', 'ladder', 6),
(1, 'cave', 'ladder', 8),
(1, 'cave', 'ladder', 10),
(1, 'cave', 'skeleton', 1),
(1, 'cave', 'skeleton', 6),
(1, 'cave', 'skeleton', 8),
(1, 'cave', 'skeleton', 10),
(1, 'cave', 'swimming trunks', 1),
(1, 'cave', 'swimming trunks', 6),
(1, 'cave', 'swimming trunks', 8),
(1, 'cave', 'swimming trunks', 10),
(1, 'field', 'mushroom', 1),
(1, 'field', 'mushroom', 6),
(1, 'field', 'mushroom', 8),
(1, 'field', 'mushroom', 10),
(1, 'house', 'tea', 1),
(1, 'house', 'tea', 6),
(1, 'house', 'tea', 8),
(1, 'house', 'tea', 10),
(21, 'inventory', 'item1', 26),
(21, 'inventory', 'item2', 26),
(1, 'lake', 'paddle', 1),
(1, 'lake', 'paddle', 6),
(1, 'lake', 'paddle', 8),
(1, 'lake', 'paddle', 10),
(3, 'second floor', 'telescope', 3);

-- --------------------------------------------------------

--
-- Table structure for table `item_req`
--

CREATE TABLE `item_req` (
  `itemName` varchar(255) NOT NULL,
  `reqName` varchar(255) NOT NULL,
  `wID` int(11) NOT NULL,
  `success_text` text NOT NULL,
  `failure_text` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `item_req`
--

INSERT INTO `item_req` (`itemName`, `reqName`, `wID`, `success_text`, `failure_text`) VALUES
('bear trap', 'skeleton', 1, '', ''),
('item2', 'item1', 21, '', ''),
('key', 'paddle', 1, '', ''),
('ladder', 'paddle', 1, '', ''),
('mushroom', 'bear trap', 1, '', ''),
('tea', 'mushroom', 1, '', '');

-- --------------------------------------------------------

--
-- Table structure for table `message`
--

CREATE TABLE `message` (
  `id` int(11) NOT NULL,
  `sender` text,
  `reciever` text,
  `body` text,
  `secretNumber` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `message`
--

INSERT INTO `message` (`id`, `sender`, `reciever`, `body`, `secretNumber`) VALUES
(1, 'bill', 'bob', 'Hello', 1),
(3, 'joe', 'francine', 'yo', 2),
(6, 'jill', 'jane', 'hey', 4),
(7, 'alice', 'bob', 'secret', 4),
(8, 'a', 'b', 'c', 2),
(9, '1', '2', '3', 4),
(10, '1', '2', '3', 4),
(11, '1', '3', '4', 5),
(12, '1', '2', '3', 4),
(13, '1', '2', '3', 7),
(14, '1', '2', '4', 8),
(15, '1', '3', '9', 27),
(16, 'hankhill', 'theworld', 'Thatboyain\'tright...', 69),
(17, 'Kenshiro', 'Opponent', 'You\'re already dead! / . \' 1 #% \\', 3),
(18, '4', '3', '2', 1),
(19, '1', '3', '5', 7),
(20, '2', '3', '4', 6),
(21, '1', '5', '3', 4),
(22, 'testy', 'testo', 'testa', 5),
(23, '2', '5', '0', 13),
(24, '8', '13', '2', 1),
(25, '1', '8', '41', 9),
(26, 'l', 'm', 'k1', 1),
(27, '1', '7', '51', 7),
(28, '1', '3', '4', 5),
(29, '1', '2', '4', 8),
(30, '1649', '0', '8', 1),
(31, '1', '4', '6', 8),
(32, '1', '9', '8', 0),
(33, 'Dr. Frankenstein', 'The World', 'IT\'S ALIVE', 1337),
(34, '1', '6', '8', 6),
(35, '1', '7', '6', 5),
(36, 'John', 'Jake', 'Yo', 3),
(37, '12', '3', '5', 7),
(38, 'Hank Hill', 'the world', 'That boy ain\'t right', 3),
(39, '2', '3', '14', 5),
(40, 'Alice', 'Bob', 'Secret', 42),
(41, 'Jamie', 'Adam', 'Myth Busted!', 1337),
(42, 'Z', 'Y', 'X', 1),
(43, 'H', 'I', 'J', 2),
(44, 'Q', 'W', 'E', 4),
(45, 'Z', 'X', 'C', 1),
(46, '1', '5', '7', 9),
(47, '20', '21', '22', 14),
(48, 'a', 's', 'd', 1),
(49, '1', '5', 'gfd', 1),
(50, 'a', 'b', '1', 1),
(51, '1', '2', '2', -4),
(52, 'wet', 'dfgj', 'dfgrht', -1),
(53, 'a', 'b', 'c', 1),
(54, 'H', 'I', 'J', 8),
(55, 'g', 'h', 'j', 8),
(56, 'q', 'w', 'e', 4),
(57, '1', '2', '3', 4),
(58, '4', '5', ':6', 7),
(59, '5', 'Y', 'Qwert', 5),
(60, 'anne', 'andy', 'ragedy', 69),
(61, '1', '2', '3', 4),
(62, 'v', 'b', 'n', 8),
(63, 'Way', 'Down', 'in the valley', 0);

-- --------------------------------------------------------

--
-- Table structure for table `path`
--

CREATE TABLE `path` (
  `pathName` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `fromPlace` varchar(255) NOT NULL,
  `toPlace` varchar(255) NOT NULL,
  `wID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `path`
--

INSERT INTO `path` (`pathName`, `description`, `fromPlace`, `toPlace`, `wID`) VALUES
('backtostart', 'goes back to start', 'place1', 'start', 21),
('door to cabin', 'The door of the house.', 'field', 'house', 1),
('door to field', 'The door of the house.', 'house', 'field', 1),
('downhill path', 'A dirt road leading downhill', 'field', 'beach', 1),
('hole down', 'A hole in the ground', 'field', 'cave', 1),
('hole up', 'A hole in the cave ceiling', 'cave', 'field', 1),
('path1', 'goes to place1', 'start', 'place1', 21),
('place2tostart', 'goes from place2 to start', 'place2', 'start', 21),
('shore', 'The water laps onto the beach.', 'beach', 'lake', 1),
('shore up', 'The water laps onto the beach.', 'lake', 'beach', 1),
('stairs down', 'Serviceable stairs', 'second floor', 'first floor', 3),
('stairs up', 'Serviceable stairs', 'first floor', 'second floor', 3),
('start2place2', 'goes from start to place2', 'start', 'place2', 21),
('uphill path', 'A dirt road leading uphill', 'beach', 'field', 1),
('uphill pathway', 'A dirt road leading uphill', 'beach', 'field', 1);

--
-- Triggers `path`
--
DELIMITER $$
CREATE TRIGGER `delete_path` BEFORE DELETE ON `path` FOR EACH ROW BEGIN
DELETE FROM save_state
	WHERE 	wID = OLD.wID AND
			sID NOT IN (SELECT sID FROM default_state);
DELETE FROM path_req WHERE pathName = OLD.pathName;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `path_req`
--

CREATE TABLE `path_req` (
  `pathName` varchar(255) NOT NULL,
  `reqName` varchar(255) NOT NULL,
  `wID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `path_req`
--

INSERT INTO `path_req` (`pathName`, `reqName`, `wID`) VALUES
('start2place2', 'item1', 21),
('door to cabin', 'key', 1),
('hole up', 'ladder', 1),
('hole down', 'rope', 1),
('shore', 'swimming trunks', 1),
('door to field', 'tea', 1);

-- --------------------------------------------------------

--
-- Table structure for table `place`
--

CREATE TABLE `place` (
  `placeName` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `wID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `place`
--

INSERT INTO `place` (`placeName`, `description`, `wID`) VALUES
('a rose patch', 'It is quite prickly here.', 4),
('a tree', 'There is a tree. It is big.', 4),
('beach', 'It is sandy here, and there is water.', 1),
('Black Lodge Entranceway', 'The forboding decor in the entrance hall goes to show visitors they are not in for a fun time.', 13),
('cave', 'It is dark. you are likely to be eaten by a grue.', 1),
('field', 'The meadow is sunny and pleasant.', 1),
('first floor', 'You can see some stairs and a secretary desk.', 3),
('house', 'It is a small but serviceable cabin.', 1),
('inventory', '', 0),
('inventory', '', 21),
('lake', 'The water is deep and murky.', 1),
('place1', 'room after start', 21),
('place2', 'another room', 21),
('Red Room', 'It is spooky and from Twin Peaks.', 13),
('second floor', 'Looking out the window, you can see your house from here.', 3),
('start', '', 0),
('start', '', 1),
('start', '', 2),
('start', '', 3),
('start', '', 4),
('start', '', 5),
('start', '', 7),
('start', '', 8),
('start', '', 9),
('start', '', 10),
('start', '', 11),
('start', '', 12),
('start', '', 14),
('start', '', 21);

--
-- Triggers `place`
--
DELIMITER $$
CREATE TRIGGER `delete_place` BEFORE DELETE ON `place` FOR EACH ROW BEGIN
DELETE FROM save_state
	WHERE 	wID = OLD.wID AND
			sID NOT IN (SELECT sID FROM default_state);
DELETE FROM `path`
	WHERE wID = OLD.wID
    AND (  fromPlace = OLD.placeName
    	OR toPlace = OLD.placeName);
DELETE FROM item_location WHERE placeName = OLD.placeName;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `player`
--

CREATE TABLE `player` (
  `username` varchar(255) NOT NULL,
  `password_hash` varchar(32) DEFAULT NULL,
  `salt` varchar(8) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `player`
--

INSERT INTO `player` (`username`, `password_hash`, `salt`) VALUES
('alice', '6384e2b2184bcbf58eccf10ca7a6563c', 'a'),
('bob', '9f9d51bc70ef21ca5c14f307980a29d8', 'b'),
('craig', '14084800449265ee16a75ea7465d01b6', 'c'),
('david', '172522ec1028ab781d9dfd17eaca4427', 'd'),
('eve', 'fa6a91ef9baa242de0b354a212e8cf82', 'e'),
('Faythe', 'b3b660e52373710356ad623839710ac9', 'Q71f495y'),
('Grover', '8b9357d92bd6a6131d34748cbb4eb14f', '/ESwKCzY'),
('Heisenberg', '752e4c65971de109af4a654c44538397', 'rkwz19mO'),
('Jacob', '5db1af4932628debb198ed1b51662f8c', 'czPRP4aD'),
('Quentin', '3644d70bc4451f87508ea632700458d6', 'jQiyEcQC'),
('user', '26f255cd8cbaa859eb2c6251ab5b44ea', 'wbgX/3z2');

--
-- Triggers `player`
--
DELIMITER $$
CREATE TRIGGER `delete_player` BEFORE DELETE ON `player` FOR EACH ROW BEGIN
DELETE FROM world WHERE owner = OLD.username;
DELETE FROM save_state WHERE sID IN
	(SELECT sID FROM player_state
     WHERE username = OLD.username);
DELETE FROM world_rating WHERE username = OLD.username;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `player_state`
--

CREATE TABLE `player_state` (
  `sID` int(11) NOT NULL,
  `username` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `player_state`
--

INSERT INTO `player_state` (`sID`, `username`) VALUES
(6, 'alice'),
(7, 'alice'),
(8, 'bob'),
(9, 'bob'),
(10, 'eve'),
(11, 'eve');

-- --------------------------------------------------------

--
-- Table structure for table `save_state`
--

CREATE TABLE `save_state` (
  `sID` int(11) NOT NULL,
  `wID` int(11) NOT NULL,
  `placeName` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `save_state`
--

INSERT INTO `save_state` (`sID`, `wID`, `placeName`) VALUES
(1, 1, 'start'),
(2, 2, 'start'),
(3, 3, 'start'),
(4, 4, 'start'),
(5, 5, 'start'),
(6, 1, 'start'),
(7, 2, 'start'),
(8, 1, 'start'),
(9, 4, 'start'),
(10, 1, 'start'),
(11, 4, 'start'),
(12, 7, 'start'),
(13, 8, 'start'),
(14, 9, 'start'),
(15, 10, 'start'),
(16, 11, 'start'),
(17, 12, 'start'),
(18, 13, 'start'),
(19, 14, 'start'),
(26, 21, 'start');

--
-- Triggers `save_state`
--
DELIMITER $$
CREATE TRIGGER `delete_save_state` BEFORE DELETE ON `save_state` FOR EACH ROW BEGIN
DELETE FROM default_state WHERE sID = OLD.sID;
DELETE FROM player_state WHERE sID = OLD.sID;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `world`
--

CREATE TABLE `world` (
  `wID` int(11) NOT NULL,
  `worldName` varchar(255) NOT NULL,
  `private` tinyint(1) NOT NULL,
  `owner` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `world`
--

INSERT INTO `world` (`wID`, `worldName`, `private`, `owner`) VALUES
(1, 'wonderland', 1, 'alice'),
(2, 'looking glass', 1, 'alice'),
(3, 'building', 0, 'bob'),
(4, 'garden', 1, 'eve'),
(5, 'eden', 0, 'eve'),
(7, '1984', 1, 'Grover'),
(8, 'bluh', 1, 'alice'),
(9, 'USA', 1, 'Grover'),
(10, 'M', 1, 'Heisenberg'),
(11, 'Y tho', 1, 'Heisenberg'),
(12, 'Test World', 1, 'Grover'),
(13, 'The Church', 1, 'Faythe'),
(14, 'Test world', 1, 'Faythe'),
(21, 'world1', 1, 'user');

--
-- Triggers `world`
--
DELIMITER $$
CREATE TRIGGER `delete_world` BEFORE DELETE ON `world` FOR EACH ROW BEGIN
DELETE FROM save_state WHERE wID = OLD.wID;
DELETE FROM world_rating WHERE wID = OLD.wID;
DELETE FROM place WHERE wID = OLD.wID;
DELETE FROM item WHERE wID = OLD.wID;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `insert_world` AFTER INSERT ON `world` FOR EACH ROW BEGIN

INSERT INTO place ( placeName, description, wID )
	VALUES ('start','',NEW.wID);
   
INSERT INTO place ( placeName, description, wID )
	VALUES ('inventory','',NEW.wID);

INSERT INTO save_state ( wID, placeName)
	VALUES ( NEW.wID, 'start' );

INSERT INTO default_state ( sID, wID )
	VALUES ( LAST_INSERT_ID(), NEW.wID );

END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `world_rating`
--

CREATE TABLE `world_rating` (
  `username` varchar(255) NOT NULL,
  `wID` int(11) NOT NULL,
  `rating` decimal(2,0) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `world_rating`
--

INSERT INTO `world_rating` (`username`, `wID`, `rating`) VALUES
('alice', 1, '7'),
('bob', 1, '10'),
('david', 3, '8'),
('eve', 1, '3'),
('eve', 2, '2');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `default_state`
--
ALTER TABLE `default_state`
  ADD PRIMARY KEY (`wID`),
  ADD KEY `sID` (`sID`);

--
-- Indexes for table `item`
--
ALTER TABLE `item`
  ADD PRIMARY KEY (`itemName`,`wID`),
  ADD KEY `wID` (`wID`);

--
-- Indexes for table `item_location`
--
ALTER TABLE `item_location`
  ADD PRIMARY KEY (`placeName`,`itemName`,`wID`,`sID`),
  ADD KEY `wID` (`wID`),
  ADD KEY `placeName` (`placeName`,`wID`),
  ADD KEY `itemName` (`itemName`,`wID`),
  ADD KEY `sID` (`sID`);

--
-- Indexes for table `item_req`
--
ALTER TABLE `item_req`
  ADD PRIMARY KEY (`itemName`,`wID`),
  ADD KEY `reqName` (`reqName`,`wID`),
  ADD KEY `wID` (`wID`);

--
-- Indexes for table `message`
--
ALTER TABLE `message`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `path`
--
ALTER TABLE `path`
  ADD PRIMARY KEY (`pathName`,`wID`),
  ADD KEY `fromPlace` (`fromPlace`,`wID`),
  ADD KEY `toPlace` (`toPlace`,`wID`),
  ADD KEY `wID` (`wID`);

--
-- Indexes for table `path_req`
--
ALTER TABLE `path_req`
  ADD PRIMARY KEY (`pathName`,`wID`),
  ADD KEY `reqName` (`reqName`,`wID`),
  ADD KEY `wID` (`wID`);

--
-- Indexes for table `place`
--
ALTER TABLE `place`
  ADD PRIMARY KEY (`placeName`,`wID`),
  ADD KEY `wID` (`wID`);

--
-- Indexes for table `player`
--
ALTER TABLE `player`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `player_state`
--
ALTER TABLE `player_state`
  ADD PRIMARY KEY (`sID`),
  ADD KEY `username` (`username`);

--
-- Indexes for table `save_state`
--
ALTER TABLE `save_state`
  ADD PRIMARY KEY (`sID`),
  ADD KEY `wID` (`wID`);

--
-- Indexes for table `world`
--
ALTER TABLE `world`
  ADD PRIMARY KEY (`wID`),
  ADD KEY `owner` (`owner`);

--
-- Indexes for table `world_rating`
--
ALTER TABLE `world_rating`
  ADD PRIMARY KEY (`username`,`wID`),
  ADD KEY `wID` (`wID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `message`
--
ALTER TABLE `message`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT for table `save_state`
--
ALTER TABLE `save_state`
  MODIFY `sID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `world`
--
ALTER TABLE `world`
  MODIFY `wID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `default_state`
--
ALTER TABLE `default_state`
  ADD CONSTRAINT `default_state_ibfk_1` FOREIGN KEY (`sID`) REFERENCES `save_state` (`sID`),
  ADD CONSTRAINT `default_state_ibfk_2` FOREIGN KEY (`wID`) REFERENCES `world` (`wID`);

--
-- Constraints for table `item`
--
ALTER TABLE `item`
  ADD CONSTRAINT `item_ibfk_1` FOREIGN KEY (`wID`) REFERENCES `world` (`wID`);

--
-- Constraints for table `item_location`
--
ALTER TABLE `item_location`
  ADD CONSTRAINT `item_location_ibfk_1` FOREIGN KEY (`wID`) REFERENCES `world` (`wID`),
  ADD CONSTRAINT `item_location_ibfk_2` FOREIGN KEY (`placeName`,`wID`) REFERENCES `place` (`placeName`, `wID`),
  ADD CONSTRAINT `item_location_ibfk_3` FOREIGN KEY (`itemName`,`wID`) REFERENCES `item` (`itemName`, `wID`),
  ADD CONSTRAINT `item_location_ibfk_4` FOREIGN KEY (`sID`) REFERENCES `save_state` (`sID`);

--
-- Constraints for table `item_req`
--
ALTER TABLE `item_req`
  ADD CONSTRAINT `item_req_ibfk_1` FOREIGN KEY (`itemName`,`wID`) REFERENCES `item` (`itemName`, `wID`),
  ADD CONSTRAINT `item_req_ibfk_2` FOREIGN KEY (`reqName`,`wID`) REFERENCES `item` (`itemName`, `wID`),
  ADD CONSTRAINT `item_req_ibfk_3` FOREIGN KEY (`wID`) REFERENCES `world` (`wID`);

--
-- Constraints for table `path`
--
ALTER TABLE `path`
  ADD CONSTRAINT `path_ibfk_1` FOREIGN KEY (`fromPlace`,`wID`) REFERENCES `place` (`placeName`, `wID`),
  ADD CONSTRAINT `path_ibfk_2` FOREIGN KEY (`toPlace`,`wID`) REFERENCES `place` (`placeName`, `wID`),
  ADD CONSTRAINT `path_ibfk_3` FOREIGN KEY (`wID`) REFERENCES `world` (`wID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

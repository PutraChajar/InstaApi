/*
Navicat MySQL Data Transfer

Source Server         : MySQL_Localhost
Source Server Version : 50505
Source Host           : localhost:3306
Source Database       : instaapp

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2021-06-11 15:55:22
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for comment
-- ----------------------------
DROP TABLE IF EXISTS `comment`;
CREATE TABLE `comment` (
  `id_comment` varchar(10) NOT NULL,
  `id_post` varchar(10) DEFAULT NULL,
  `id_user` varchar(10) DEFAULT NULL,
  `comment` varchar(1000) DEFAULT NULL,
  `date_comment` datetime DEFAULT NULL,
  PRIMARY KEY (`id_comment`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of comment
-- ----------------------------

-- ----------------------------
-- Table structure for followers
-- ----------------------------
DROP TABLE IF EXISTS `followers`;
CREATE TABLE `followers` (
  `id_user` varchar(10) NOT NULL,
  `follower` varchar(10) NOT NULL,
  PRIMARY KEY (`id_user`,`follower`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of followers
-- ----------------------------

-- ----------------------------
-- Table structure for love
-- ----------------------------
DROP TABLE IF EXISTS `love`;
CREATE TABLE `love` (
  `id_post` varchar(10) NOT NULL,
  `id_user` varchar(10) NOT NULL,
  `date_like` datetime DEFAULT NULL,
  PRIMARY KEY (`id_post`,`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of love
-- ----------------------------

-- ----------------------------
-- Table structure for post
-- ----------------------------
DROP TABLE IF EXISTS `post`;
CREATE TABLE `post` (
  `id_post` varchar(10) NOT NULL,
  `id_user` varchar(10) DEFAULT NULL,
  `photo` varchar(50) DEFAULT NULL,
  `caption` varchar(500) DEFAULT NULL,
  `date_post` datetime DEFAULT NULL,
  PRIMARY KEY (`id_post`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of post
-- ----------------------------
INSERT INTO `post` VALUES ('PS21000001', 'US21000001', 'PS21000001.png', 'ini saya putra', '2021-04-23 14:15:38');
INSERT INTO `post` VALUES ('PS21000002', 'US21000002', 'PS21000002.png', 'ini saya ibnu', '2021-04-23 14:21:18');
INSERT INTO `post` VALUES ('PS21000003', 'US21000003', 'PS21000003.png', 'ini saya chajar', '2021-04-23 14:24:16');

-- ----------------------------
-- Table structure for user
-- ----------------------------
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id_user` varchar(10) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `photo` varchar(50) DEFAULT NULL,
  `date_register` datetime DEFAULT NULL,
  PRIMARY KEY (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of user
-- ----------------------------
INSERT INTO `user` VALUES ('US21000001', 'putra', '4388159474443efc1a78d239ad52aad0f6056d7e', 'putra@gmail.com', 'putra', 'US21000001.png', '2021-04-23 14:14:27');
INSERT INTO `user` VALUES ('US21000002', 'ibnu', '0c43fb21604c2658cc5ce7a96c867c886ab69894', 'ibnu@gmail.com', 'ibnu', 'US21000002.png', '2021-04-23 14:16:22');
INSERT INTO `user` VALUES ('US21000003', 'chajar', '27b53e94ff8a2a0b2e7919f4988fefa2240355cd', 'chajar@gmail.com', 'chajar', 'US21000003.png', '2021-04-23 14:23:01');
INSERT INTO `user` VALUES ('US21000004', 'putrachajar', '10d0b55e0ce96e1ad711adaac266c9200cbc27e4', 'putra.chajar7@gmail.com', 'Putra Ibnu Chajar', 'US21000004.jpeg', '2021-04-23 18:46:38');

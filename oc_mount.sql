/*
Navicat MySQL Data Transfer

Source Server         : sunny
Source Server Version : 50617
Source Host           : localhost:3306
Source Database       : owncloudtest

Target Server Type    : MYSQL
Target Server Version : 50617
File Encoding         : 65001

Date: 2015-09-18 14:15:52
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `oc_mount`
-- ----------------------------
DROP TABLE IF EXISTS `oc_mount`;
CREATE TABLE `oc_mount` (
  `id` int(64) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `category` tinyint(1) DEFAULT '0' COMMENT '用户或者组标示符(0：user；1：group；)',
  `name` varchar(64) COLLATE utf8_bin DEFAULT NULL COMMENT '用户uid或者组名',
  `storage_name` varchar(64) COLLATE utf8_bin DEFAULT NULL COMMENT '使用该存储对应的文件名',
  `class` varchar(64) COLLATE utf8_bin DEFAULT NULL COMMENT '使用的存储类',
  `options` varchar(512) COLLATE utf8_bin DEFAULT NULL COMMENT '存储具体内容',
  `priority` int(11) DEFAULT NULL COMMENT '优先级',
  `storage_id` varchar(11) COLLATE utf8_bin DEFAULT NULL COMMENT '存储的id（从oc_storage中返回）',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

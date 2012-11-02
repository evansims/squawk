-- phpMyAdmin SQL Dump
-- version 2.10.0.2
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: Jul 26, 2007 at 09:54 PM
-- Server version: 5.0.27
-- PHP Version: 4.4.4

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- --------------------------------------------------------

-- 
-- Table structure for table `donators`
-- 

CREATE TABLE `donators` (
  `id` int(11) NOT NULL auto_increment,
  `avatar` varchar(128) NOT NULL,
  `donated` varchar(12) NOT NULL,
  `amount` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table structure for table `notifications`
-- 

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL auto_increment,
  `uid` varchar(32) NOT NULL,
  `posted` varchar(13) NOT NULL,
  `tmessage` varchar(255) NOT NULL,
  `received` longtext NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `notifications`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `notification_queue`
-- 

CREATE TABLE `notification_queue` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `message` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- 
-- Dumping data for table `notification_queue`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `squawkers`
-- 

CREATE TABLE `squawkers` (
  `id` int(11) NOT NULL auto_increment,
  `avatar` varchar(128) NOT NULL,
  `uid` varchar(255) NOT NULL,
  `seen` varchar(12) NOT NULL,
  `twitter` varchar(64) NOT NULL,
  `jaiku` varchar(64) NOT NULL,
  `tumblr` varchar(64) NOT NULL,
  `img` varchar(255) NOT NULL,
  `img_check` varchar(13) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table structure for table `squawks`
-- 

CREATE TABLE `squawks` (
  `id` int(11) NOT NULL auto_increment,
  `avatar` varchar(128) NOT NULL,
  `region` varchar(255) NOT NULL,
  `x` int(11) NOT NULL default '0',
  `y` int(11) NOT NULL default '0',
  `z` int(11) NOT NULL,
  `posted` varchar(12) NOT NULL,
  `message` varchar(160) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table structure for table `stats`
-- 

CREATE TABLE `stats` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(128) NOT NULL,
  `content` varchar(128) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table structure for table `tags`
-- 

CREATE TABLE `tags` (
  `avatar` varchar(128) NOT NULL,
  `built` varchar(13) NOT NULL,
  `data` longtext NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

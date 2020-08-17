
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `type` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '用户类型;1:admin;2:会员',
  `role_id` int(11)  NOT NULL DEFAULT '0' COMMENT '角色id',
  `gender` tinyint(3) NOT NULL DEFAULT '0' COMMENT '性别1男2女0未知',
  `birthday` int(11) NOT NULL DEFAULT '0' COMMENT '生日',
  `last_login_time` int(11) NOT NULL DEFAULT '0' COMMENT '最后登录时间',
  `score` int(11) NOT NULL DEFAULT '0' COMMENT '用户积分',
  `coin` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '金币',
  `balance` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '余额',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '注册时间',
  `status` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '用户状态;0:禁用,1:正常',
  `user_name` varchar(60) NOT NULL DEFAULT '' COMMENT '用户名',
  `password` varchar(64) NOT NULL DEFAULT '' COMMENT '登录密码',
  `nickname` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '用户昵称',
  `openid` varchar(60) NOT NULL DEFAULT '',
  `user_email` varchar(100) NOT NULL DEFAULT '' COMMENT '用户登录邮箱',
  `avatar` varchar(255) NOT NULL DEFAULT '' COMMENT '用户头像',
  `login_count` int(11) NOT NULL DEFAULT 0 COMMENT '登录次数',
  `last_login_ip` varchar(15) NOT NULL DEFAULT '' COMMENT '最后登录ip',
  `mobile` varchar(20) NOT NULL DEFAULT '' COMMENT '中国手机不带国家代码，国际手机号格式为：国家代码-手机号',
  `more` text COMMENT '扩展属性',
  PRIMARY KEY (`id`),
  KEY `user_name` (`user_name`),
  KEY `nickname` (`nickname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户表';


DROP TABLE IF EXISTS `user_follow`;
CREATE TABLE `user_follow` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `follow_user_id` bigint(20) NOT NULL DEFAULT '0' COMMENT '关注用户ID',
  `create_time` int(10) DEFAULT '0' COMMENT '关注时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `follow_user_id` (`follow_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户关注表';


DROP TABLE IF EXISTS `favorite`;
CREATE TABLE `favorite` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `article_id` bigint(20) NOT NULL DEFAULT '0' COMMENT '文章id',
  `create_time` int(10) DEFAULT '0' COMMENT '时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户收藏表';


DROP TABLE IF EXISTS `navigation`;
CREATE TABLE `navigation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL COMMENT '父 id',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态;1:显示;0:隐藏',
  `list_order` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `name` varchar(50)  NOT NULL DEFAULT '' COMMENT '菜单名称',
  `target` varchar(10) NOT NULL DEFAULT '' COMMENT '打开方式',
  `href` varchar(100) NOT NULL DEFAULT '' COMMENT '链接',
  `catalog_id` int(11) NOT NULL DEFAULT '0' COMMENT '分类id',
  `icon` varchar(20) NOT NULL DEFAULT '' COMMENT '图标',
  `type` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态;1:外链;0:内链',
  `create_time` int(10) DEFAULT '0' COMMENT '时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='前台导航菜单表';


DROP TABLE IF EXISTS `catalog`;
CREATE TABLE `catalog` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '分类id',
  `parent_id` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '分类父id',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态,1:发布,0:不发布',
  `list_order` float NOT NULL DEFAULT '10000' COMMENT '排序',
  `name` varchar(200) NOT NULL DEFAULT '' COMMENT '分类名称',
  `path` varchar(255) NOT NULL DEFAULT '' COMMENT '分类层级关系路径',
  `seo_title` varchar(100) NOT NULL DEFAULT '',
  `seo_keywords` varchar(255) NOT NULL DEFAULT '',
  `seo_description` varchar(255) NOT NULL DEFAULT '',
  `image` varchar(255) NOT NULL DEFAULT '' COMMENT '缩略图',
  `create_time` int(10) DEFAULT '0' COMMENT '时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='文章分类表';


DROP TABLE IF EXISTS `article`;
CREATE TABLE `article`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Article 主键',
  `title` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '标题',
  `user_id` int(11) NOT NULL DEFAULT 0 COMMENT '作者ID',
  `catalog_id` int(11) NULL DEFAULT 0 COMMENT '分类ID',
  `wechat_id` int(11) NULL DEFAULT 0 COMMENT '公众号id',
  `list_order` int(11) NOT NULL DEFAULT 0 COMMENT '排序标识 越大越靠前',
  `content` text NOT NULL COMMENT '文章内容',
  `picture` varchar(255) NULL DEFAULT '' COMMENT '封面',
  `view` int(11) NULL DEFAULT 0 COMMENT '浏览量',
  `is_hot` tinyint(3) NULL DEFAULT 0 COMMENT '1推荐',
  `status` tinyint(2) NULL DEFAULT 1 COMMENT '状态1已发布2待审核3已删除',
  `abstract` varchar(255) NULL DEFAULT '' COMMENT '摘要',
  `source_url` varchar(255) NULL DEFAULT '' COMMENT '来源连接',
  `tag` varchar(255) NULL DEFAULT '' COMMENT '标签',
  `create_time` int(11) NULL DEFAULT NULL,
  `update_time` int(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  key `title`(`title`),
  key `list_order`(`list_order`)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT='文章表';


DROP TABLE IF EXISTS `option`;
CREATE TABLE `option` (
  `option_name` varchar(64) NOT NULL DEFAULT '' COMMENT '配置名',
  `option_value` varchar(1000) COMMENT '配置值',
  `option_explain` varchar(200)  NOT NULL COMMENT '配置说明',
  PRIMARY KEY (`option_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='全站配置表';



DROP TABLE IF EXISTS `link`;
CREATE TABLE `link` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `list_order` float NOT NULL DEFAULT '10000' COMMENT '排序',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '友情链接地址',
  `name` varchar(30) NOT NULL DEFAULT '' COMMENT '友情链接名称',
  `image` varchar(100) NOT NULL DEFAULT '' COMMENT '友情链接图标',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='友情链接表';


DROP TABLE IF EXISTS `slide`;
CREATE TABLE `slide` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '类型,1:显示;0:隐藏',
  `list_order` float NOT NULL DEFAULT '10000' COMMENT '排序',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '幻灯片名称',
  `image` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '幻灯片图片',
  `url` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '幻灯片链接',
  `description` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT '幻灯片描述',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='幻灯片表';


DROP TABLE IF EXISTS `filter`;
CREATE TABLE `filter` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `keyword` varchar(1000) NOT NULL DEFAULT '' COMMENT '过滤文本',
  `create_time` int(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='过滤关键词';

DROP TABLE IF EXISTS `collect`;
CREATE TABLE `collect` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `title` varchar(50) NOT NULL DEFAULT '0' COMMENT '标题',
  `content` text COMMENT '内容',
  `desc` varchar(255) DEFAULT '' COMMENT '文章描述',
  `url` varchar(255) DEFAULT '' COMMENT '文章链接',
  `weixin_id` varchar(255) DEFAULT '' COMMENT '微信号',
  `weixin_nickname` varchar(255) DEFAULT '' COMMENT '微信名',
  `weixin_introduce` varchar(255) DEFAULT '' COMMENT '微信介绍',
  `weixin_avatar` varchar(255) DEFAULT '' COMMENT '微信头像',
  `picture` varchar(100) DEFAULT '' COMMENT '封面',
  `publish_time` int(11) DEFAULT '0' COMMENT '采集时间',
  `create_time` int(11) DEFAULT '0' COMMENT '采集时间',
  `sign` varchar(100) DEFAULT '' COMMENT '签名',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
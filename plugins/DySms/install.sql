/*
// +----------------------------------------------------------------------
// | 海豚PHP框架 [ DolphinPHP ]
// +----------------------------------------------------------------------
// | 版权所有 2016~2017 河源市卓锐科技有限公司 [ http://www.zrthink.com ]
// +----------------------------------------------------------------------
// | 官方网站: http://dolphinphp.com
// +----------------------------------------------------------------------
// | 开源协议 ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
*/


SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `dp_plugin_dysms`
-- ----------------------------
DROP TABLE IF EXISTS `dp_plugin_dysms`;
CREATE TABLE `dp_plugin_dysms` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(32) NOT NULL DEFAULT '' COMMENT '模板名称',
  `code` varchar(128) NOT NULL DEFAULT '' COMMENT '模板id',
  `sign_name` varchar(128) NOT NULL DEFAULT '' COMMENT '短信签名',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '添加时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='短信模板表';
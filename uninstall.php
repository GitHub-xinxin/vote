<?php
$sql = "
	drop table `ims_goddess_advs`;
	drop table `ims_goddess_announce`;
	drop table `ims_goddess_banners`;
	drop table `ims_goddess_bbsreply`;
	drop table `ims_goddess_data`;
	drop table `ims_goddess_iplist`;
	drop table `ims_goddess_iplistlog`;
	drop table `ims_goddess_provevote`;
	drop table `ims_goddess_provevote_picarr`;
	drop table `ims_goddess_provevote_name`;
	drop table `ims_goddess_provevote_voice`;
	drop table `ims_goddess_tags`;
	drop table `ims_goddess_reply`;
	drop table `ims_goddess_reply_share`;
	drop table `ims_goddess_reply_huihua`;
	drop table `ims_goddess_reply_display`;
	drop table `ims_goddess_reply_vote`;
	drop table `ims_goddess_reply_body`;
	drop table `ims_goddess_templates`;
	drop table `ims_goddess_templates_designer`;
	drop table `ims_goddess_templates_designer_menu`;
	drop table `ims_goddess_votelog`;
	drop table `ims_goddess_order`;
	drop table `ims_goddess_voteer`;
	drop table `ims_goddess_onlyoauth`;
	drop table `ims_goddess_qunfa`;
	drop table `ims_goddess_vote_shuapiao`;
	drop table `ims_goddess_pnametag`;
	drop table `ims_goddess_jifen`;
	drop table `ims_goddess_jifen_gift`;
	drop table `ims_goddess_user_gift`;
	drop table `ims_goddess_user_zsgift`;
	drop table `ims_goddess_message`;
	drop table `ims_goddess_orderlog`;
	drop table `ims_goddess_counter`;
";
pdo_run($sql);
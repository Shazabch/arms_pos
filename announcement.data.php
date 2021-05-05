<?php
/*
9/27/2018 3:20 PM Andy
- Add Annoucement list can load from file.

8/16/2019 2:57 PM Andy
- Add announcement for 2019-08-21 releases.

8/26/2019 10:35 AM Andy
- Add announcement for 2019-08-28 releases.

9/6/2019 2:03 PM Andy
- Add announcement for 2019-09-06 releases.

9/10/2019 9:56 AM Andy
- Add announcement for 2019-09-10 releases.

9/17/2019 1:16 PM Andy
- Add announcement for 2019-09-17 releases.

9/23/2019 11:42 AM Andy
- Add announcement for 2019-09-23 releases.

9/30/3019 9:40 AM Andy
- Add announcement for 2019-09-30 releases.

10/1/2019 10:13 AM Andy
- Add announcement for 2019-10-01 releases.

10/7/2019 10:34 AM Andy
- Add announcement for 2019-10-07 releases.

10/9/2019 12:10 PM Andy
- Add announcement for 2019-10-09 releases.

10/14/2019 10:10 AM Andy
- Add announcement for 2019-10-14.

10/21/2019 11:43 AM Andy
- Add announcement for 2019-10-21.

10/29/2019 10:21 AM Andy
- Add announcement for 2019-10-29.

11/18/2019 3:11 PM Andy
- Add announcement for 2019-11-18.

11/25/2019 10:51 AM Andy
- Add announcement for 2019-11-25.

12/2/2019 11:49 AM Andy
- Add announcement for 2019-12-02.

12/9/2019 9:36 AM Andy
- Add announcement for 2019-12-09.

12/17/2019 10:14 AM Andy
- Add announcement for 2019-12-16.

12/18/2019 1:44 PM Andy
- Add announcement for 2019-12-18.

12/30/2019 9:45 AM Andy
- Add announcement for 2019-12-30.

1/7/2020 1:54 PM Andy
- Add announcement for 2020-01-07.

1/13/2020 9:58 AM Andy
- Add announcement for 2020-01-13.

1/20/2020 10:48 AM Andy
- Add announcement for 2020-01-20.

2/3/2020 10:31 AM Andy
- Add announcement for 2020-02-03.

2/24/2020 11:47 AM Andy
- Add announcement for 2020-02-24.

3/2/2020 3:33 PM Andy
- Add announcement for 2020-03-02.

4/27/2020 11:10 AM Andy
- Add announcement for 2020-04-01 and 2020-04-27.

5/11/2020 3:24 PM Andy
- Add announcement for 2020-05-11.

5/26/2020 2:08 PM Andy
- Add announcement for 2020-05-26.

6/15/2020 10:12 AM Andy
- Add announcement for 2020-06-15.

6/22/2020 10:17 AM Andy
- Add announcement for 2020-06-22.

7/6/2020 3:42 PM Andy
- Add announcement for 2020-07-06.

7/6/2020 3:50 PM Andy
- Add announcement for 2020-07-06 Part 2.

7/20/2020 11:31 AM Andy
- Add announcement for 2020-07-20.

7/28/2020 2:55 PM Andy
- Add announcement for 2020-07-27.

9/7/2020 2:03 PM Andy
- Add announcement for 2020-09-07.

9/9/2020 4:10 PM Andy
- Add announcement for 2020-09-09.

9/22/2020 2:22 PM Andy
- Add announcement for 2020-09-22.

10/1/2020 2:27 PM Andy
- Add announcement for 2020-10-01.

10/5/2020 2:23 PM Andy
- Add announcement for 2020-10-05.

10/16/2020 1:16 PM Andy
- Add announcement for 2020-10-16.

10/19/2020 1:00 PM Andy
- Add announcement for 2020-10-19.

10/26/2020 3:45 PM Andy
- Add announcement for 2020-10-26.

11/2/2020 10:57 AM Andy
- Add announcement for 2020-11-02.

11/5/2020 9:42 AM Andy
- Add announcement for 2020-11-05.

11/9/2020 11:04 AM Andy
- Add announcement for 2020-11-09.

11/23/2020 12:13 PM Andy
- Add announcement for 2020-11-23.

12/7/2020 2:21 PM Andy
- Add announcement for 2020-12-07.

12/14/2020 4:42 PM Andy
- Add announcement for 2020-12-14.

12/21/2020 2:00 PM Andy
- Add announcement for 2020-12-21.

12/28/2020 2:04 PM Andy
- Add announcement for 2020-12-28.

1/11/2021 2:49 PM Andy
- Add announcement for 2021-01-11.

1/18/2021 1:54 PM Andy
- Add announcement for 2021-01-18 (ARMS POS Live 207.1).

1/18/2021 2:06 PM Andy
- Add announcement for 2021-01-18 (Backend).

1/25/2021 1:57 PM Andy
- Add announcement for 2021-01-25.

1/28/2021 10:22 AM Andy
- Add announcement for 2021-01-28 (ARMS POS Live v208).

2/1/2021 3:16 PM Andy
- Add announcement for 2021-02-01.

2/22/2021 1:10 PM Andy
- Add announcement for 2021-02-22.

3/1/2021 3:07 PM Andy
- Add announcement for 2021-03-01.

3/16/2021 2:38 PM Andy
- Add announcement for 2021-03-16 (ARMS POS Live v209).

3/29/2021 3:03 PM Andy
- Add announcement for 2021-03-29 (ARMS POS Live v209.1).

3/30/2021 12:30 PM Andy
- Add announcement for 2021-03-30.

4/1/2021 4:32 PM Andy
- Add announcement for 2021-04-01 (ARMS POS Live v209.2).

4/5/2021 2:26 PM Andy
- Add announcement for 2021-04-05. (Sales Order show RSP)
*/
$announcementList = array(
	/*'2018082901' => array(
		'title' => 'SST Amendment Notice',
		'url' => 'https://drive.google.com/file/d/183CKuJG2RtWCcMw-UWis4Up6NA5jLiqs/view'
	),
	'2018092701' => array(
		'title' => 'New SMS Regulation',
		'content' => '<h1>HIGHLY IMPORTANT: NEW REGULATION AND REMINDER FROM MAXIS TO APPEND BRAND NAME IN MESSAGE CONTENT</h1><br />

<div class="stdframe">
Dear Clients,<br /><br />

Please note that Maxis, Malaysia has imposed a new regulation effective 1st October 2018.<br /><br />

You are required to include the brand name/name of the party providing the content in each SMS message. If you fail to include brand name/name of the party in the SMS message, such message will be deemed as a message from an international brand which we are entitled to charge RM0.30 for each SMS message.<br /><br />

Thanks & Best Regards<br /><br />

</div>
<br />

<div class="stdframe">
Example 1: <br />
LAZADA: your tac code is 123456<br /><br />
Lazada is brand name<br />
</div>
<br />

<div class="stdframe">
Example 2: <br />
ARMS: your birthday reminder xxxxxx<br /><br />

ARMS is brand name
</div>'),*/
	
	'2021031601' => array(
		'title' => 'ARMS POS Live 209',
		'url' => 'https://mailchi.mp/0300997592d1/arms-pos-v209'
	),
	'2021032901' => array(
		'title' => 'ARMS POS Live 209.1',
		'url' => 'https://mailchi.mp/f54e216a0224/arms-pos-v2091'
	),
	'2021040101' => array(
		'title' => 'ARMS POS Live 209.2',
		'url' => 'https://mailchi.mp/f48f87663fd4/arms-pos-v2092'
	),
	'2021030103' => array(
		'title' => 'PO Summary Add Export Excel',
		'url' => 'https://mailchi.mp/d29d893a70ff/arms-general-enhancement-3-03032021'
	),
	'2021030104' => array(
		'title' => 'Member Listing Add Export CSV',
		'url' => 'https://mailchi.mp/0bb3c623a12d/arms-general-enhancement-4-03032021'
	),
	'2021030105' => array(
		'title' => 'Added Price Checker Menu',
		'url' => 'https://mailchi.mp/d2eba3c3043c/arms-general-enhancement-5-03032021'
	),
	'2021033001' => array(
		'title' => 'Closing Stock by SKU Report Enhancement',
		'url' => 'https://mailchi.mp/715af53413e5/arms-general-enhancement-31032021'
	),
	'2021040501' => array(
		'title' => 'Sales Order show RSP',
		'url' => 'https://mailchi.mp/ebbea909710b/arms-general-enhancement-07042021'
	)
);
?>
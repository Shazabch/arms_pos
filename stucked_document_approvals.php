<?php

include_once("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('APPROVAL_ON_BEHALF')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'APPROVAL_ON_BEHALF', BRANCH_CODE), "/index.php");

include_once("stucked_document_approvals.include.php");
$STUCKED_DOCUMENT_APPROVALS = new STUCKED_DOCUMENT_APPROVALS('Stucked Document Approvals');

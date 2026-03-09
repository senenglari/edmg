<?php
/* ---------------
 ENCRYPTION
------------------- */
define('ENCODE_ID', 'MDS');
define('ENCODE_SECRET', 'Mitra!@#$');

/* ---------------
 STATUS CODE
------------------- */
define('GLOBAL_SUCCESS_RESPONSE', '200');
define('GLOBAL_UNAUTHORIZED_RESPONSE', '401');

define('AUTH_UNAUTHORIZED', '401');
define('AUTH_ERROR_VALIDATE', '402');

define('SUCCESS_STATUS_CODE', '100');
define('ERROR_STATUS_CODE', '000');
define('INVALID_STATUS_CODE', '001');
define('UNAUTHORIZED_STATUS_CODE', '403');

define('SUCCESS_MESSAGE', 'Succeed');
define('FAILED_MESSAGE', 'Failed');

/* ---------------
 COMMON
------------------- */
define('PAGINATION', 8);

/* ---------------
 DOCUMENT DIRECTORY
------------------- */
define('DOCUMENT_TEMP_DIR', '/temp');
define('DOCUMENT_DIR', '/document');
define('DOCUMENT_DIR_OUTGOING', '/outgoing');
define('DOCUMENT_DIR_COMMENT', '/comments');
define('DOCUMENT_INTERFACE', '/interface');

/* ---------------
 DOCUMENT ISSUE STATUS
------------------- */
/*
Mapping harus sama dengan ref_issue_status table
*/

define('STATUS_IFC', 1);
define('STATUS_RE_IFC', 2);

define('STATUS_IFA', 3);
define('STATUS_RE_IFA', 4);

define('STATUS_IFR', 5);
define('STATUS_RE_IFR', 6);

define('STATUS_IFI', 7);
define('STATUS_RE_IFI', 8);

define('STATUS_DONE', 9);

define('STATUS_ONLY_IFI', 13);
define('STATUS_ONLY_IDC', 1);
define('STATUS_ONLY_IFI_CONSTRUCTION', 18);

/* ---------------
 DOCUMENT FLOW GROUP
------------------- */

define('STATUS_FLOW_IFC', '1,2');
define('STATUS_FLOW_IFA', '3,4');
define('STATUS_FLOW_IFR', '5,6');
define('STATUS_FLOW_IFI', '7,8');

/* ---------------
 RETURN STATUS
------------------- */

define('RETURN_APPROVE', 1);
define('RETURN_REJECT', 2);
define('RETURN_COMMENT', 3);

/* ---------------
 REVIEW ROLE
------------------- */

define('ROLE_REVIEWER', 'RESPONSIBLE');
define('ROLE_OWNER', 'OWNER');
define('ROLE_APPROVER', 'APPROVER');

/* ---------------
 REVIEW SOURCE
------------------- */

define('REVIEW_INTERNAL', 'INTERNAL');
define('REVIEW_EXTERNAL', 'EXTERNAL');

/* ---------------
 DEFAULT SYSTEM CONFIG
------------------- */

define('DEFAULT_APPROVER_DOCUMENT', 22);

/* ---------------
 DOCUMENT WORKFLOW NEXT STATUS
------------------- */

function getNextStatusApprove($status)
{
    switch ($status) {

        case STATUS_IFC:
            return STATUS_IFA;

        case STATUS_IFA:
            return STATUS_DONE;

        case STATUS_IFR:
            return STATUS_DONE;

        case STATUS_IFI:
            return STATUS_DONE;

        default:
            return $status;
    }
}

function getNextStatusReject($status)
{
    switch ($status) {

        case STATUS_IFC:
            return STATUS_RE_IFC;

        case STATUS_IFA:
            return STATUS_RE_IFA;

        case STATUS_IFR:
            return STATUS_RE_IFR;

        case STATUS_IFI:
            return STATUS_RE_IFI;

        default:
            return $status;
    }
}
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
    # -------------------
    define('AUTH_UNAUTHORIZED', '401');
    define('AUTH_ERROR_VALIDATE', '402');
    # -------------------
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
    define('STATUS_APPROVAL_IDC', '2,3,10');
    define('STATUS_APPROVAL_IFA', '8,4,12,16'); // 11 ADM
    define('STATUS_APPROVAL_AFC', '8,6');
    define('STATUS_APPROVAL_AFC_IFU', '4,9,12,16');
    define('STATUS_APPROVAL_AFD', '4,8');
    define('STATUS_APPROVAL_ADM', '11');
    define('STATUS_APPROVAL_IFI', '13,14');
    define('STATUS_APPROVAL_IFC', '3,15');
    define('STATUS_ONLY_IFI', '13');
    define('STATUS_ONLY_IDC', '1');
    define('STATUS_ONLY_IFI_CONSTRUCTION', '18');
    define('DEFAULT_REVISION_NUMBER_IFU', 119);
    define('DEFAULT_REVISION_NUMBER_AFC', 101);
    define('DEFAULT_APPROVER_DOCUMENT', 22);
    define('DOCUMENT_INTERFACE', '/interface');


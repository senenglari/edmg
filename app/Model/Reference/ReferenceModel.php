<?php

namespace App\Model\Reference;

use Illuminate\Database\Eloquent\Model;
use DB;
use Auth;

class ReferenceModel extends Model
{
    public function getSelectDiscipline()
    {
        $query = DB::select("SELECT discipline_id AS id, CONCAT(ref_discipline.name, ' - ', ref_department.name) as name 
                             FROM   ref_discipline INNER JOIN ref_department ON ref_discipline.department_id = ref_department.department_id
                             WHERE  ref_discipline.status = 1 ORDER BY ref_department.name ASC, ref_discipline.name ASC");

        return $query;
    }

    public function getSelectDisciplineConcat()
    {
        $query = DB::select("SELECT CONCAT(discipline_id, '|', ref_discipline.department_id) AS id, CONCAT(ref_discipline.name, ' - ', ref_department.name) as name 
                             FROM   ref_discipline INNER JOIN ref_department ON ref_discipline.department_id = ref_department.department_id
                             WHERE  ref_discipline.status = 1 ORDER BY ref_department.name ASC, ref_discipline.name ASC");

        return $query;
    }

    public function getSelectDepartment()
    {
        $query = DB::select("SELECT department_id AS id, name as name FROM ref_department where status = 1 ORDER BY name ASC");

        return $query;
    }

    public function getSelectDocument()
    {
        $query = DB::select("SELECT document_id AS id, document_no as name FROM document where status != 6 ORDER BY name ASC");

        return $query;
    }

    public function getSelectDocumentVendor($vendor_id)
    {
        $query = DB::select("SELECT document_id AS id, document_no as name FROM document where status NOT IN (0,6) AND vendor_id = '$vendor_id' ORDER BY name ASC");

        return $query;
    }

    public function getSelectIssueStatus()
    {
        $query = DB::select("SELECT issue_status_id AS id, name FROM ref_issue_status where status = 1 ORDER BY name ASC");

        return $query;
    }
    public function getSelectIssueStatusWithoutIFI()
    {
        $query = DB::select("SELECT issue_status_id AS id, name FROM ref_issue_status where status = 1 AND issue_status_id NOT IN (13,14,18) ORDER BY name ASC");

        return $query;
    }
    public function getSelectIssueStatusIFI()
    {
        $query = DB::select("SELECT issue_status_id AS id, name FROM ref_issue_status where status = 1 AND issue_status_id = " . STATUS_ONLY_IFI . " ORDER BY name ASC");

        return $query;
    }
    public function getSelectIssueStatusIDC()
    {
        $query = DB::select("SELECT issue_status_id AS id, name FROM ref_issue_status where issue_status_id = " . STATUS_ONLY_IDC . " ORDER BY name ASC");

        return $query;
    }
    public function getSelectReturnStatus()
    {
        $query = DB::select("SELECT return_status_id AS id, name FROM ref_return_status where status = 1 ORDER BY name ASC");

        return $query;
    }
    public function getSelectDocumentStatus()
    {
        $query = DB::select("SELECT document_status_id AS id, name FROM ref_document_status where status = 1 ORDER BY name ASC");

        return $query;
    }
    public function getSelectIssueStatusIFIContruction()
    {
        $query = DB::select("SELECT issue_status_id AS id, name FROM ref_issue_status where status = 1 AND issue_status_id = " . STATUS_ONLY_IFI_CONSTRUCTION . " ORDER BY name ASC");

        return $query;
    }

    public function getSelectDocumentType()
    {
        $query = DB::select("SELECT document_type_id AS id, name FROM ref_document_type where status = 1 ORDER BY name ASC");

        return $query;
    }

    public function getSelectCompany()
    {
        $query = DB::select("SELECT company_id AS id, name FROM ref_company where status = 1 ORDER BY name ASC");

        return $query;
    }

    public function getSelectArea()
    {
        $query = DB::select("SELECT area_id AS id, name FROM ref_area where status = 1 ORDER BY name ASC");

        return $query;
    }

    public function getSelectVendor()
    {
        $query = DB::select("SELECT vendor_id AS id, name FROM ref_vendor where status = 1 ORDER BY name ASC");

        return $query;
    }

    public function getSelectProject()
    {
        $query = DB::select("SELECT project_id AS id, project_name AS name FROM project where status = 1 ORDER BY name ASC");

        return $query;
    }

    public function getSelectProjectUser()
    {
        $query = DB::select("SELECT project_id AS id, project_name AS name FROM project JOIN sys_users ON project.`vendor_id` = sys_users.vendor_id where sys_users.id = " . Auth::user()->id . " ORDER BY project_id");
        return $query;
    }

    public function getSelectVendorUser()
    {
        $query = DB::select("SELECT ref_vendor.vendor_id AS id, ref_vendor.name AS name FROM ref_vendor JOIN sys_users ON ref_vendor.`vendor_id` = sys_users.vendor_id where sys_users.id = " . Auth::user()->id . " ORDER BY ref_vendor.vendor_id");
        return $query;
    }

    public function getSelectUser()
    {
        $query = DB::select("SELECT a.id as id, CONCAT(a.full_name,' - ',c.name, ' , ' , b.name ) AS name FROM sys_users AS a JOIN ref_position AS b ON a.position_id = b.position_id JOIN ref_department as c ON a.department_id = c.department_id where a.user_status = 1 ORDER BY name ASC");

        return $query;
    }

    public function getSelectPosition()
    {
        $query = DB::select("SELECT position_id AS id, name FROM ref_position where status = 1 ORDER BY name ASC");

        return $query;
    }

    public function getSelectIssueStatusComments($id)
    {
        // if ($id < 3) {
        //     $query = DB::select("SELECT issue_status_id AS id, name FROM ref_issue_status where issue_status_id IN (2,3) AND status = 1 ORDER BY id ASC");
        // } else {
        //     $query = DB::select("SELECT issue_status_id AS id, name FROM ref_issue_status where issue_status_id =4 AND status = 1 ORDER BY id ASC");
        // }

        $query = DB::select("SELECT issue_status_id AS id, name FROM ref_issue_status where issue_status_id IN ($id) AND status = 1 ORDER BY id ASC");
        // $query = DB::select("SELECT issue_status_id AS id, name FROM ref_issue_status where status = 1 ORDER BY id ASC");

        return $query;
    }

    public function getSelectDocumentOutgoing($id)
    {
        $query = DB::select("SELECT incoming_transmittal_detail_id AS id, document_no as name FROM document where status = 3 AND vendor_id=$id AND incoming_transmittal_detail_id != 0 ORDER BY name ASC");

        return $query;
    }

    public function getSelectExtention()
    {
        $query = DB::select("SELECT extention_id AS id, name FROM ref_extention where status = 1 ORDER BY name ASC");

        return $query;
    }

    public function getSelectCountry()
    {
        $query = DB::select("SELECT country_id AS id, name FROM ref_country where status = 1 ORDER BY name ASC");

        return $query;
    }

    public function getSelectEmail()
    {
        $query = DB::select("SELECT email AS id, email as name FROM sys_users where user_status = 1 ORDER BY email ASC");

        return $query;
    }

    public function getSelectDocumentIdc($vendor_id)
    {
        $query = DB::select("SELECT document_id AS id, document_no as name FROM document where status NOT IN (0,6) AND vendor_id = '$vendor_id' ORDER BY name ASC");

        return $query;
    }

    public function getSelectIdcDocument()
    {
        $query = DB::select("SELECT document_id AS id, document_no as name FROM document where issue_status_id = 1 and status = 1 ORDER BY name ASC");

        return $query;
    }
}

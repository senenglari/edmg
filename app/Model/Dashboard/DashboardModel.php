<?php

namespace App\Model\Dashboard;

use Illuminate\Database\Eloquent\Model;
use DB;
use Auth;
use Carbon\Carbon;
use App\Model\Sys\LogModel;

class DashboardModel extends Model
{
    public function getSummaryScreenshot()
    {
        try {
            $query  = DB::table("ref_vendor as vendor")
                ->leftJoin('document', 'document.vendor_id', '=', 'vendor.vendor_id')
                ->select(
            'vendor.vendor_id',
                    'vendor.name',
                    DB::raw("COUNT(IF(document.issue_status_id IN (10, 15), 1, NULL)) AS ifc_status"),
                    DB::raw("COUNT(IF(document.issue_status_id IN (3, 8), 1, NULL)) AS ifa_status"),
                    DB::raw("COUNT(IF(document.issue_status_id = 12, 1, NULL)) AS afd_status"),
                    DB::raw("COUNT(IF(document.issue_status_id IN (4, 9), 1, NULL)) AS afc_status")
                )
                ->groupBy('vendor.vendor_id', 'vendor.name')
                ->get();

            return array("status" => true, "data" => $query);
        } catch (\Exception $e) {
            return array("status" => false, "data" => []);
        }
    }

    public function getSummary()
    {
        try {
            $query  = DB::table("document")
                ->select(
                    DB::RAW("COUNT(IF((issue_status_id = 10 OR issue_status_id = 15), 1, NULL)) AS ifc_status"),
                    DB::RAW("COUNT(IF((issue_status_id = 3 OR issue_status_id = 8), 1, NULL)) AS ifa_status"),
                    DB::RAW("COUNT(IF((issue_status_id = 12), 1, NULL)) AS afd_status"),
                    DB::RAW("COUNT(IF((issue_status_id = 4 OR issue_status_id = 9), 1, NULL)) AS afc_status")
                )
                ->whereNotIn("status", [0, 6]);

            if (session()->has("SES_DASHBOARD_VENDOR_SELECTED") != "") {
                $query->where("document.vendor_id", session()->get("SES_DASHBOARD_VENDOR_SELECTED"));
            }

            if (Auth::user()->vendor_id != 0) {
                $query->where("document.vendor_id", Auth::user()->vendor_id);
            }

            $result = $query->first();

            return array("status" => true, "data" => $result);
        } catch (\Exception $e) {
            return array("status" => false, "data" => []);
        }
    }

    public function getIFCList()
    {
        try {
            $query  = DB::table("document")->select(
                "document.document_no",
                "document.document_title",
                "document.status",
                DB::Raw("DATE_FORMAT(document.deadline, '%d/%m/%Y') AS deadline_date"),
                DB::Raw("COUNT(comment.comment_id) AS unit"),
                DB::Raw("GROUP_CONCAT(comment.status ORDER BY comment.order_no) AS list_status"),
                db::Raw("GROUP_CONCAT(comment.role ORDER BY comment.order_no) AS list_role"),
                DB::Raw("GROUP_CONCAT(sys_users.full_name ORDER BY comment.order_no) AS list_name"),
                DB::Raw("(CASE document.status WHEN 1 THEN 'Unissued' WHEN 2 THEN 'Waiting for reviewer' WHEN 3 THEN 'Waiting for compiler' WHEN 4 THEN 'Waiting for return' WHEN 5 THEN 'Waiting for approval' WHEN 7 THEN 'Waiting for view' WHEN 99 THEN 'Stored' WHEN 88 THEN 'Reject' WHEN 6 THEN 'Done' END) AS status_code")
            )
                ->leftJoin('assignment', function ($join) {
                    $join->on("document.document_id", "assignment.document_id");
                    $join->on("document.incoming_transmittal_detail_id", "assignment.incoming_transmittal_detail_id");
                })
                ->leftjoin("comment", "assignment.assignment_id", "comment.assignment_id")
                ->leftjoin("sys_users", "comment.user_id", "sys_users.id")
                ->whereNotIn("document.status", [0, 6])->whereIn("document.issue_status_id", [10, 15])
                ->groupBy("document.document_no", "document.document_title", "document.deadline", "document.status")
                ->LIMIT(5);

            if (session()->has("SES_DASHBOARD_VENDOR_SELECTED") != "") {
                $query->where("document.vendor_id", session()->get("SES_DASHBOARD_VENDOR_SELECTED"));
            }

            if (Auth::user()->vendor_id != 0) {
                $query->where("document.vendor_id", Auth::user()->vendor_id);
            }

            $result = $query->get();

            return array("status" => true, "data" => $result);
        } catch (\Exception $e) {
            return array("status" => false, "data" => []);
        }
    }

    public function getIFC()
    {
        $query  = DB::table("document")->select(
            "document.document_no",
            "document.document_title",
            DB::Raw("DATE_FORMAT(document.deadline, '%d/%m/%Y') AS deadline"),
            DB::Raw("GROUP_CONCAT(CASE comment.status WHEN 1 AND comment.role = 'RESPONSIBILITY' THEN 'O' WHEN 1 AND comment.role = 'REVIEWER' THEN '-' WHEN 2 THEN 'V' END ORDER BY comment.order_no SEPARATOR '|') AS Progress"),
            DB::Raw("GROUP_CONCAT(sys_users.full_name ORDER BY comment.order_no) AS list_name"),
            DB::Raw("(CASE document.status WHEN 1 THEN 'Unissued' WHEN 2 THEN 'Waiting for reviewer' WHEN 3 THEN 'Waiting for compiler' WHEN 4 THEN 'Waiting for return' WHEN 5 THEN 'Waiting for approval' WHEN 7 THEN 'Waiting for view' WHEN 99 THEN 'Stored' WHEN 88 THEN 'Reject' WHEN 6 THEN 'Done' END) AS status_code")
        )
            ->leftJoin('assignment', function ($join) {
                $join->on("document.document_id", "assignment.document_id");
                $join->on("document.incoming_transmittal_detail_id", "assignment.incoming_transmittal_detail_id");
            })
            ->leftjoin("comment", "assignment.assignment_id", "comment.assignment_id")
            ->leftjoin("sys_users", "comment.user_id", "sys_users.id")
            ->whereNotIn("document.status", [0, 6])->whereIn("document.issue_status_id", [10, 15])
            ->groupBy("document.document_no", "document.document_title", "document.deadline", "document.status");

        if (session()->has("SES_DASHBOARD_VENDOR_SELECTED") != "") {
            $query->where("document.vendor_id", session()->get("SES_DASHBOARD_VENDOR_SELECTED"));
        }

        if (Auth::user()->vendor_id != 0) {
            $query->where("document.vendor_id", Auth::user()->vendor_id);
        }

        return $query;
    }

    public function getIFAList()
    {
        try {
            $query  = DB::table("document")->select(
                "document.document_no",
                "document.document_title",
                "document.status",
                DB::Raw("DATE_FORMAT(document.deadline, '%d/%m/%Y') AS deadline_date"),
                DB::Raw("COUNT(comment.comment_id) AS unit"),
                DB::Raw("GROUP_CONCAT(comment.status ORDER BY comment.order_no) AS list_status"),
                db::Raw("GROUP_CONCAT(comment.role ORDER BY comment.order_no) AS list_role"),
                DB::Raw("GROUP_CONCAT(sys_users.full_name ORDER BY comment.order_no) AS list_name"),
                DB::Raw("(CASE document.status WHEN 1 THEN 'Unissued' WHEN 2 THEN 'Waiting for reviewer' WHEN 3 THEN 'Waiting for compiler' WHEN 4 THEN 'Waiting for return' WHEN 5 THEN 'Waiting for approval' WHEN 7 THEN 'Waiting for view' WHEN 99 THEN 'Stored' WHEN 88 THEN 'Reject' WHEN 6 THEN 'Done' END) AS status_code")
            )
                ->leftJoin('assignment', function ($join) {
                    $join->on("document.document_id", "assignment.document_id");
                    $join->on("document.incoming_transmittal_detail_id", "assignment.incoming_transmittal_detail_id");
                })
                ->leftjoin("comment", "assignment.assignment_id", "comment.assignment_id")
                ->leftjoin("sys_users", "comment.user_id", "sys_users.id")
                ->whereNotIn("document.status", [0, 6])->whereIn("document.issue_status_id", [3, 8])
                ->groupBy("document.document_no", "document.document_title", "document.deadline", "document.status")
                ->LIMIT(5);

            if (session()->has("SES_DASHBOARD_VENDOR_SELECTED") != "") {
                $query->where("document.vendor_id", session()->get("SES_DASHBOARD_VENDOR_SELECTED"));
            }

            if (Auth::user()->vendor_id != 0) {
                $query->where("document.vendor_id", Auth::user()->vendor_id);
            }

            $result = $query->get();

            return array("status" => true, "data" => $result);
        } catch (\Exception $e) {
            return array("status" => false, "data" => []);
        }
    }

    public function getIFA()
    {
        $query  = DB::table("document")->select(
            "document.document_no",
            "document.document_title",
            DB::Raw("DATE_FORMAT(document.deadline, '%d/%m/%Y') AS deadline"),
            DB::Raw("GROUP_CONCAT(CASE comment.status WHEN 1 AND comment.role = 'RESPONSIBILITY' THEN 'X' WHEN 1 AND comment.role = 'REVIEWER' THEN '-' WHEN 2 THEN 'V' END ORDER BY comment.order_no SEPARATOR '|') AS Progress"),
            DB::Raw("GROUP_CONCAT(sys_users.full_name ORDER BY comment.order_no) AS list_name"),
            DB::Raw("(CASE document.status WHEN 1 THEN 'Unissued' WHEN 2 THEN 'Waiting for reviewer' WHEN 3 THEN 'Waiting for compiler' WHEN 4 THEN 'Waiting for return' WHEN 5 THEN 'Waiting for approval' WHEN 7 THEN 'Waiting for view' WHEN 99 THEN 'Stored' WHEN 88 THEN 'Reject' WHEN 6 THEN 'Done' END) AS status_code")
        )
            ->leftJoin('assignment', function ($join) {
                $join->on("document.document_id", "assignment.document_id");
                $join->on("document.incoming_transmittal_detail_id", "assignment.incoming_transmittal_detail_id");
            })
            ->leftjoin("comment", "assignment.assignment_id", "comment.assignment_id")
            ->leftjoin("sys_users", "comment.user_id", "sys_users.id")
            ->whereNotIn("document.status", [0, 6])->whereIn("document.issue_status_id", [3, 8])
            ->groupBy("document.document_no", "document.document_title", "document.deadline", "document.status");

        if (session()->has("SES_DASHBOARD_VENDOR_SELECTED") != "") {
            $query->where("document.vendor_id", session()->get("SES_DASHBOARD_VENDOR_SELECTED"));
        }

        if (Auth::user()->vendor_id != 0) {
            $query->where("document.vendor_id", Auth::user()->vendor_id);
        }
        return $query;
    }

    public function getAFDList()
    {
        try {
            $query  = DB::table("document")->select(
                "document.document_no",
                "document.document_title",
                "document.status",
                DB::Raw("DATE_FORMAT(document.deadline, '%d/%m/%Y') AS deadline_date"),
                DB::Raw("COUNT(comment.comment_id) AS unit"),
                DB::Raw("GROUP_CONCAT(comment.status ORDER BY comment.order_no) AS list_status"),
                db::Raw("GROUP_CONCAT(comment.role ORDER BY comment.order_no) AS list_role"),
                DB::Raw("GROUP_CONCAT(sys_users.full_name ORDER BY comment.order_no) AS list_name"),
                DB::Raw("(CASE document.status WHEN 1 THEN 'Unissued' WHEN 2 THEN 'Waiting for reviewer' WHEN 3 THEN 'Waiting for compiler' WHEN 4 THEN 'Waiting for return' WHEN 5 THEN 'Waiting for approval' WHEN 7 THEN 'Waiting for view' WHEN 99 THEN 'Stored' WHEN 88 THEN 'Reject' WHEN 6 THEN 'Done' END) AS status_code")
            )
                ->leftJoin('assignment', function ($join) {
                    $join->on("document.document_id", "assignment.document_id");
                    $join->on("document.incoming_transmittal_detail_id", "assignment.incoming_transmittal_detail_id");
                })
                ->leftjoin("comment", "assignment.assignment_id", "comment.assignment_id")
                ->leftjoin("sys_users", "comment.user_id", "sys_users.id")
                ->whereNotIn("document.status", [0, 6])->whereIn("document.issue_status_id", [12])
                ->groupBy("document.document_no", "document.document_title", "document.deadline", "document.status")
               ->LIMIT(5);

            if (session()->has("SES_DASHBOARD_VENDOR_SELECTED") != "") {
                $query->where("document.vendor_id", session()->get("SES_DASHBOARD_VENDOR_SELECTED"));
            }

            if (Auth::user()->vendor_id != 0) {
                $query->where("document.vendor_id", Auth::user()->vendor_id);
            }

            $result = $query->get();

            return array("status" => true, "data" => $result);
        } catch (\Exception $e) {
            return array("status" => false, "data" => []);
        }
    }

    public function getAFD()
    {
        $query  = DB::table("document")->select(
            "document.document_no",
            "document.document_title",
            DB::Raw("DATE_FORMAT(document.deadline, '%d/%m/%Y') AS deadline"),
            DB::Raw("GROUP_CONCAT(CASE comment.status WHEN 1 AND comment.role = 'RESPONSIBILITY' THEN 'X' WHEN 1 AND comment.role = 'REVIEWER' THEN '-' WHEN 2 THEN 'V' END ORDER BY comment.order_no SEPARATOR '|') AS Progress"),
            DB::Raw("GROUP_CONCAT(sys_users.full_name ORDER BY comment.order_no) AS list_name"),
            DB::Raw("(CASE document.status WHEN 1 THEN 'Unissued' WHEN 2 THEN 'Waiting for reviewer' WHEN 3 THEN 'Waiting for compiler' WHEN 4 THEN 'Waiting for return' WHEN 5 THEN 'Waiting for approval' WHEN 7 THEN 'Waiting for view' WHEN 99 THEN 'Stored' WHEN 88 THEN 'Reject' WHEN 6 THEN 'Done' END) AS status_code")
        )
            ->leftJoin('assignment', function ($join) {
                $join->on("document.document_id", "assignment.document_id");
                $join->on("document.incoming_transmittal_detail_id", "assignment.incoming_transmittal_detail_id");
            })
            ->leftjoin("comment", "assignment.assignment_id", "comment.assignment_id")
            ->leftjoin("sys_users", "comment.user_id", "sys_users.id")
            ->whereNotIn("document.status", [0, 6])->whereIn("document.issue_status_id", [12])
            ->groupBy("document.document_no", "document.document_title", "document.deadline", "document.status");
        if (session()->has("SES_DASHBOARD_VENDOR_SELECTED") != "") {
            $query->where("document.vendor_id", session()->get("SES_DASHBOARD_VENDOR_SELECTED"));
        }

        if (Auth::user()->vendor_id != 0) {
            $query->where("document.vendor_id", Auth::user()->vendor_id);
        }
        return $query;
    }

    public function getAFCList()
    {
        try {
            $query  = DB::table("document")->select(
                "document.document_no",
                "document.document_title",
                "document.status",
                DB::Raw("DATE_FORMAT(document.deadline, '%d/%m/%Y') AS deadline_date"),
                DB::Raw("COUNT(comment.comment_id) AS unit"),
                DB::Raw("GROUP_CONCAT(comment.status ORDER BY comment.order_no) AS list_status"),
                db::Raw("GROUP_CONCAT(comment.role ORDER BY comment.order_no) AS list_role"),
                DB::Raw("GROUP_CONCAT(sys_users.full_name ORDER BY comment.order_no) AS list_name"),
                DB::Raw("(CASE document.status WHEN 1 THEN 'Unissued' WHEN 2 THEN 'Waiting for reviewer' WHEN 3 THEN 'Waiting for compiler' WHEN 4 THEN 'Waiting for return' WHEN 5 THEN 'Waiting for approval' WHEN 7 THEN 'Waiting for view' WHEN 99 THEN 'Stored' WHEN 88 THEN 'Reject' WHEN 6 THEN 'Done' END) AS status_code")
            )
                ->leftJoin('assignment', function ($join) {
                    $join->on("document.document_id", "assignment.document_id");
                    $join->on("document.incoming_transmittal_detail_id", "assignment.incoming_transmittal_detail_id");
                })
                ->leftjoin("comment", "assignment.assignment_id", "comment.assignment_id")
                ->leftjoin("sys_users", "comment.user_id", "sys_users.id")
                ->whereNotIn("document.status", [0, 6])->whereIn("document.issue_status_id", [4, 9])
                ->groupBy("document.document_no", "document.document_title", "document.deadline", "document.status")
               ->LIMIT(5);

            if (session()->has("SES_DASHBOARD_VENDOR_SELECTED") != "") {
                $query->where("document.vendor_id", session()->get("SES_DASHBOARD_VENDOR_SELECTED"));
            }

            if (Auth::user()->vendor_id != 0) {
                $query->where("document.vendor_id", Auth::user()->vendor_id);
            }

            $result = $query->get();

            return array("status" => true, "data" => $result);
        } catch (\Exception $e) {
            return array("status" => false, "data" => []);
        }
    }

    public function getAFC()
    {
        $query  = DB::table("document")->select(
            "document.document_no",
            "document.document_title",
            DB::Raw("DATE_FORMAT(document.deadline, '%d/%m/%Y') AS deadline"),
            DB::Raw("GROUP_CONCAT(CASE comment.status WHEN 1 AND comment.role = 'RESPONSIBILITY' THEN 'X' WHEN 1 AND comment.role = 'REVIEWER' THEN '-' WHEN 2 THEN 'V' END ORDER BY comment.order_no SEPARATOR '|') AS Progress"),
            DB::Raw("GROUP_CONCAT(sys_users.full_name ORDER BY comment.order_no) AS list_name"),
            DB::Raw("(CASE document.status WHEN 1 THEN 'Unissued' WHEN 2 THEN 'Waiting for reviewer' WHEN 3 THEN 'Waiting for compiler' WHEN 4 THEN 'Waiting for return' WHEN 5 THEN 'Waiting for approval' WHEN 7 THEN 'Waiting for view' WHEN 99 THEN 'Stored' WHEN 88 THEN 'Reject' WHEN 6 THEN 'Done' END) AS status_code")
        )
            ->leftJoin('assignment', function ($join) {
                $join->on("document.document_id", "assignment.document_id");
                $join->on("document.incoming_transmittal_detail_id", "assignment.incoming_transmittal_detail_id");
            })
            ->leftjoin("comment", "assignment.assignment_id", "comment.assignment_id")
            ->leftjoin("sys_users", "comment.user_id", "sys_users.id")
            ->whereNotIn("document.status", [0, 6])->whereIn("document.issue_status_id", [4, 9])
            ->groupBy("document.document_no", "document.document_title", "document.deadline", "document.status");
        if (session()->has("SES_DASHBOARD_VENDOR_SELECTED") != "") {
            $query->where("document.vendor_id", session()->get("SES_DASHBOARD_VENDOR_SELECTED"));
        }

        if (Auth::user()->vendor_id != 0) {
            $query->where("document.vendor_id", Auth::user()->vendor_id);
        }
        return $query;
    }

    public function getWaitingApproval()
    {
        try {
            $query  = DB::table("document")
                ->select("document.document_no", "sys_users.full_name", "document.document_title")
                ->leftjoin("sys_users", "document.approved_by", "sys_users.id")
                ->where("document.status", 4)
                ->get();

            return array("status" => true, "data" => $query);
        } catch (\Exception $e) {
            return array("status" => false, "data" => []);
        }
    }

    public function getWaitingRevision()
    {
        try {
            $query  = DB::table("document")
                ->select("document.document_no", "document.document_title")
                ->where("document.status", 3)
                ->get();

            return array("status" => true, "data" => $query);
        } catch (\Exception $e) {
            return array("status" => false, "data" => []);
        }
    }

    public function getWaitingReview()
    {
        try {
            DB::statement("DROP  TEMPORARY TABLE IF EXISTS temp_review;

                           CREATE TEMPORARY TABLE temp_review AS (
                                SELECT  document.document_id, document.document_no, COUNT(comment.comment_id) AS unit, COUNT(IF((comment.status = '2'),1,NULL)) AS done
                                        , COUNT(IF((comment.status = '1'),1,NULL)) AS progress, GROUP_CONCAT(sys_users.full_name ORDER BY comment.order_no) AS list_name
                                FROM    document INNER JOIN assignment ON (document.document_id = assignment.document_id AND document.incoming_transmittal_detail_id = assignment.incoming_transmittal_detail_id)
                                INNER   JOIN comment ON assignment.assignment_id = comment.assignment_id
                                INNER   JOIN sys_users ON comment.user_id = sys_users.id 
                                WHERE   document.status = 2
                                GROUP   BY document.document_id, document.document_no
                           );

                           SELECT * FROM temp_review;");

            $query  = DB::table("document")
                ->select(
                    "document.document_no",
                    "document.status",
                    "document.document_title",
                    "comment.user_id",
                    "comment.status",
                    "comment.order_no",
                    "sys_users.full_name",
                    DB::RAW("DATE_FORMAT(document.deadline, '%d/%m/%Y') AS deadline_date"),
                    "temp_review.unit",
                    "temp_review.done",
                    "temp_review.progress",
                    "temp_review.list_name"
                )
                ->join("assignment", function ($join) {
                    $join->on("document.document_id", "assignment.document_id")->On("document.incoming_transmittal_detail_id", "assignment.incoming_transmittal_detail_id");
                })
                ->join("comment", "assignment.assignment_id", "comment.assignment_id")
                ->join("sys_users", "comment.user_id", "sys_users.id")
                ->join("temp_review", "document.document_id", "temp_review.document_id")
                ->where("document.status", 2)
                ->where("comment.status", 1)
                ->get();

            return array("status" => true, "data" => $query);
        } catch (\Exception $e) {
            throw $e;
            return array("status" => false, "data" => []);
        }
    }
}

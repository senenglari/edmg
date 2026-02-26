<?php

namespace App\Http\Controllers;

use App\Model\Dashboard\DashboardModel;
use GuzzleHttp\Client;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use Request;
use Spatie\Browsershot\Browsershot;
use Illuminate\Support\Str;

class ScreenShotController extends Controller
{
    protected $PROT_SideMenu, $PROT_Parent, $PROT_ModuleId, $PROT_ModuleName;

    public function __construct() {
        $this->qDashboard       = new DashboardModel;
    }

    /**
     *
     */
    public function index()
    {
        $data["summary"] = $this->qDashboard->getSummaryScreenshot();
        return view("screenshot.index", $data);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * @throws \Spatie\Browsershot\Exceptions\CouldNotTakeBrowsershot
     */
    public function sendDashboardController()
    {
        $url = 'https://edms.pog.co.id/screenshot/index';

        $filename = 'dashboard_' . Str::random(10) . '_' . date('Y-m-d H:i') . '.png';

        $storagePath = 'public/screenshots/' . $filename;

        Browsershot::url($url)
            ->setNodeBinary('/home/www-data/.nvm/versions/node/v16.20.2/bin/node')
            ->setNpmBinary('/home/www-data/.nvm/versions/node/v16.20.2/bin/npm')
            ->setChromePath('/usr/bin/google-chrome')
//            ->windowSize(1920, 900)
            ->fullPage()
            ->noSandbox()
            ->timeout(360)
            ->save(storage_path('app/' . $storagePath));

        $response = $this->sendDashboardToTelegram('app/' . $storagePath);

        if (!$response->getData()->status) {
            return response()->json(["error" => $response->getData()->message]);
        }

        return response()->json(["success" => true]);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendDashboardToTelegram($file)
    {
        $filePath = storage_path($file);
        $fileName = basename($filePath);

        $token  = config('services.telegram.bot_token');
        $chatId = config('services.telegram.chat_id');

        if (!file_exists($filePath)) {
            return response()->json([
                'status' => false,
                'message' => 'File screenshot tidak ditemukan'
            ], 404);
        }

        try {
            $client = new Client();

            $response = $client->post("https://api.telegram.org/bot{$token}/sendPhoto", [
                'multipart' => [
                    [
                        'name'     => 'chat_id',
                        'contents' => $chatId,
                    ],
                    [
                        'name'     => 'caption',
                        'contents' => '📊 Dashboard Report',
                    ],
                    [
                        'name'     => 'photo',
                        'contents' => fopen($filePath, 'r'),
                        'filename' => $fileName,
                    ],
                ],
            ]);

            $statusCode = $response->getStatusCode();
            $body = json_decode((string) $response->getBody(), true);

            if ($statusCode !== 200 || empty($body['ok'])) {
                return response()->json([
                    'status' => false,
                    'message' => 'Telegram API error',
                    'response' => $body
                ], 500);
            }

            // hapus file setelah sukses
            unlink($filePath);

            return response()->json([
                'status' => true,
                'message' => 'Dashboard berhasil dikirim ke Telegram'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Exception saat kirim Telegram',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

<?php

namespace App\Http\Controllers\Api\Notification;

use App\Events\MailNotificationEvent;
use App\Helper\GeneralHelper;
use App\Http\Controllers\Api\BaseController;
use App\Http\Modules\NotificationTemplateModule;
use App\Http\Requests\Notification\EmailSendRequest;
use App\Notifications\Notification;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Validator;

class NotificationController extends BaseController
{
    /**
     * The service name.
     * 
     * @var String
     */
    protected $name = 'notification service';

    /**
     * NotificationTemplate module.
     * 
     * @var NotificationTemplateModule
     */
    private $module;

    public function __construct()
    {
        $this->module = new NotificationTemplateModule();
    }

    /**
     * send email notification to receivers
     * 
     * @param EmailSendRequest $request
     * @return \Illuminate\Http\Response $response
     */
    public function emailSend(EmailSendRequest $request)
    {
        $validated = $request->validated();
        $name = data_get($validated, 'name');
        $to = data_get($validated, 'to');
        $attachments = data_get($validated, 'attachments', []);
        $cc = data_get($validated, 'cc');
        $data = GeneralHelper::toCamelCase(data_get($validated, 'data'));

        try {
            $template = $this->module->findOneBy('name', $name);
            $rules = data_get($template, 'required_data');
            $rules = json_decode($rules, true);
    
            // - validate required data
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                $response = [
                    'data' => null,
                    'message' => 'failed to pass the validator',
                    'errors' => $validator->errors()->all(),
                    'statusCode' => 422
                ];
                throw new HttpResponseException(
                    response()->json($response, 422)
                );
            }

            // - send email via event
            $notification = new Notification($data, $template);
            $event = new MailNotificationEvent($notification, $to, $cc, $attachments);
            event($event);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->throwError(404, $e->getMessage());
        } catch (\Exception $e) {
            $this->throwError(400, $e->getMessage());
        }

        return $this->sendResponse();
    }
}
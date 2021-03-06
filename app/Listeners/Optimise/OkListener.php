<?php

namespace plunner\Listeners\Optimise;

use plunner\Events\Optimise\OkEvent;

class OkListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  OkEvent $event
     * @return void
     */
    public function handle(OkEvent $event)
    {
        //
        \Log::info('Meeting correctly optimised (company id = ' . $event->getCompany()->id . ')');
        $company = $event->getCompany()->fresh();
        //send email to company
        self::sendCompanyEmail($company->email);
        //send emails to employees
        $employees = $company->employees()->with(['meetings'=>function($query){
            $query->where('start_time', '>=', new \DateTime());
        }])->get();
        self::sendPushs('New meeting scheduled', $employees->get(0)->meetings->get(0)['title'] . ' - ' . $employees->get(0)->meetings->get(0)['start_time'], $employees->get(0)->meetings->get(0)['id']);
        foreach ($employees as $employee)
            self::sendEmployeeEmail($employee->email, $employee->meetings);
    }

    /**
     * @param string $email
     */
    static private function sendCompanyEmail($email)
    {
        \Mail::queue('emails.optimise.ok.company', [], function ($message) use ($email) {
            $message->from(config('mail.from.address'), config('mail.from.name'));
            $message->to($email)->subject('Meetings optimised');
        });
    }

    static private function sendPushs($title, $message, $additionalData = '')
    {
        $clients = explode(';', config('app.gcm_clients'));
        foreach ($clients as $client) {
            self::sendPushNotification($client, $message, $title, $additionalData);
        }
        //self::sendPushNotification($clients, $message, $title);
    }

    static private function sendPushNotification($to, $message, $title, $additionalData)
    {
        // replace API
        //\Log::info('GCM registration id: ' . $to);
        $registrationIds = array($to);
        $msg = array
        (
            'message' => $message,
            'title' => $title,
            'vibrate' => 1,
            'sound' => 1,
            'additionalData' => $additionalData,

            // you can also add images, additionalData
        );
        $fields = array
        (
            'registration_ids' => $registrationIds,
            'data' => $msg
        );
        $headers = array
        (
            'Authorization: key=' . config('app.gcm_key'),
            'Content-Type: application/json'
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://android.googleapis.com/gcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        curl_close($ch);
        //echo $result;
        \Log::info('GCM results: ' . $result);
    }

    /**
     * @param string $email
     * @param \Illuminate\Support\Collection $meetings
     */
    static private function sendEmployeeEmail($email, $meetings)
    {
        \Mail::queue('emails.optimise.ok.employee', ['meetings' => $meetings], function ($message) use ($email) {
            $message->from(config('mail.from.address'), config('mail.from.name'));
            $message->to($email)->subject('Meetings of next week');
        });
    }
}

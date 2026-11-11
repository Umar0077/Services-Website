<?php

namespace App\Notifications;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewLeadNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Lead $lead) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $lead = $this->lead->loadMissing('state');

        return (new MailMessage)
            ->subject('New HVAC request: '.($lead->service_label ?? 'General inquiry').' — '.$lead->zip)
            ->line('A new request was submitted on the website.')
            ->line('Service: '.($lead->service_label ?? 'Not specified'))
            ->line('Location: '.collect([$lead->city_name, $lead->state?->code, $lead->zip])->filter()->implode(', '))
            ->action('Open in admin', url('/admin/leads/'.$lead->id))
            ->line('Contact details are available in the admin panel.');
    }
}

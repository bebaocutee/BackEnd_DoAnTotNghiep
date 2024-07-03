<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OtpMail extends Notification
{
    use Queueable;

    private $otp;
    private $user;
    /**
     * Create a new notification instance.
     */
    public function __construct($otp, $user)
    {
        $this->otp = $otp;
        $this->user = $user;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Mã OTP của bạn là " . $this->otp)
            ->greeting("Xin chào " . $this->user->name ?? 'bạn' . "!")
            ->line('Mã OTP của bạn là: ' . $this->otp . '. Vui lòng không chia sẻ mã này với người khác.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}

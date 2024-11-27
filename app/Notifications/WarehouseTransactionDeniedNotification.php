<?php

namespace App\Notifications;

use App\Broadcasting\HrmsNotifyCreatorChannel;
use App\Enums\ApprovalModels;
use App\Models\WarehouseTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Notification;

class WarehouseTransactionDeniedNotification extends Notification
{
    use Queueable;

    private $token;
    private $model;
    public $id;

    /**
     */
    public function __construct($token, WarehouseTransaction $model)
    {
        $this->token = $token;
        $this->model = $model;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [HrmsNotifyCreatorChannel::class];
    }

    public function getToken()
    {
        return $this->token;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => "Warehouse Transaction request has been DENIED.",
            'module' => "Inventory",
            'request_type' => ApprovalModels::WarehouseTransaction->name,
            'request_id' => $this->model->id,
            'action' => "View"
        ];
    }
}

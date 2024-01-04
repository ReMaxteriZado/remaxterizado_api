<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class ContactForm extends Notification
{
    use Queueable;

    private $data = null;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        // Set the data
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        Log::info($this->data['details']);
        return (new MailMessage)
                    ->from('website@zaidabg.com', 'Zaida Betancor Guerra')
                    ->subject('Solicitud de contacto')
                    ->greeting('¡Hola!')
                    ->line('Se ha enviado un correo solicitnado información sobre una sesión con los siguientes datos:')
                    ->line('Nombre: ' . $this->data['name'])
                    ->line('Teléfono: ' . $this->data['phone'])
                    ->line('Tipo de evento: ' . $this->data['eventType'])
                    ->line('Fecha del evento: ' . $this->data['eventDate'])
                    ->line('Mensaje: ' . $this->data['details'])
                    ->salutation('Un saludo');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}

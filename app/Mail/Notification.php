<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

// class Notification extends Mailable
// {
//     use Queueable, SerializesModels;
//     public $data = [];

//     public function __construct($data)
//     {
//         $this->data = $data;

//     }

//     public function build()
//     {
//         return $this->from('rrhh@jpingenieria.pe', 'RRHH JP Ingenieria y Servicios.')
//             ->subject($this->data["subject"])
//             ->view('rrhh.preboleta.plantilla_email')
//             ->with("data", $this->data);
//     }
// }
class Notification extends Mailable
{
    use Queueable, SerializesModels;

    public $data = [];
    public $pdf; // <<--- agregamos la propiedad para recibir el PDF

    public function __construct($data, $pdf = null)
    {
        $this->data = $data;
        $this->pdf = $pdf; // <<--- guardamos el pdf en la clase
    }

    public function build()
    {
        $mail = $this->from('rrhh@jpingenieria.pe', 'RRHH JP Ingenieria y Servicios.')
            ->subject($this->data["subject"])
            ->view('rrhh.preboleta.plantilla_email')
            ->with("data", $this->data);

        // Si hay PDF, lo adjuntamos
        if ($this->pdf) {
            $mail->attachData($this->pdf, 'preboleta.pdf', [
                'mime' => 'application/pdf',
            ]);
        }

        return $mail;
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: noe
 * Date: 28/02/19
 * Time: 09:22 PM
 */

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ComprobanteMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The demo object instance.
     *
     * @var Demo
     */
    public $mail;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($mail)
    {
        $this->mail = $mail;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $email = $this->from('noe_tipo@hotmail.com')->view('mails.comprobante')->subject('COMPROBANTE ELECTRÓNICO')->cc([$this->mail->empresa_emisor_mail, 'noe_tipo@upeu.edu.pe']);
        foreach ($this->mail->archivos as $filePath) {
            $email->attach($filePath);
        }
        return $email;


    }
}

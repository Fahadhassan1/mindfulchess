<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;

class Admin
{
    use Notifiable;

    /**
     * The admin's email address.
     *
     * @var string
     */
    public $email;

    /**
     * Create a new admin instance.
     *
     * @param  string  $email
     * @return void
     */
    public function __construct($email)
    {
        $this->email = $email;
    }

    /**
     * Get the notification routing information for the email driver.
     *
     * @return string
     */
    public function routeNotificationForMail()
    {
        return $this->email;
    }
}

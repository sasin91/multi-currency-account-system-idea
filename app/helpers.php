<?php

if (! function_exists('points_for')) {
    /**
     * Calculate the points equal to given amount.
     *
     * @param  int $amount
     * @return int
     */
    function points_for(int $amount)
    {
        return (int)($amount / 100) * 0.8;
    }
}

if (!function_exists('is_email')) {
    /**
     * Determines whether an email was given.
     *
     * @param  mixed $email
     * @return boolean
     */
    function is_email($email)
    {
        return is_string($email) && filter_var($email, FILTER_VALIDATE_EMAIL);
    }
}

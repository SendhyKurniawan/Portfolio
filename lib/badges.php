<?php

// Projects and logs added within this many days get a "NEW!" sticker
const NEW_BADGE_DAYS = 60;

function is_recent(?string $when, int $now, int $days = NEW_BADGE_DAYS): bool
{
    if ($when === null || trim($when) === '') {
        return false;
    }
    $time = strtotime($when);
    if ($time === false) {
        return false;
    }
    return $time >= $now - $days * 86400;
}

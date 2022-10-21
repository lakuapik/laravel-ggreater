<?php

function get_all_timezones(): array
{
    return DateTimeZone::listIdentifiers(DateTimeZone::ALL);
}

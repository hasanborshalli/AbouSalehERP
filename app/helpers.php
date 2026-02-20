<?php

function activeRoute($pattern)
{
    return request()->routeIs($pattern) ? 'is-active' : '';
}
<?php

$url = $kirby->request()->url();

$targetUrl = str_replace("cms.", "", $url);

if (!str_contains($targetUrl, '.json')) {
  go($targetUrl);
}

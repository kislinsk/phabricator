<?php

function disallowUnwantedBots() {
  $out = array();
  $names = array(
    'AhrefsBot',
    'dotbot',
    'PetalBot',
    'SemrushBot'
  );

  foreach ($names as $name) {
    $out[] = 'User-agent: '.$name;
    $out[] = 'Disallow: /';
    $out[] = '';
  }

  return $out;
}

function isDKFZUser($user) {
  $email_address = new PhutilEmailAddress($user->loadPrimaryEmailAddress());
  $domain_name = phutil_utf8_strtolower($email_address->getDomainName());
  return preg_match('/^dkfz(?:-heidelberg)?\.de$/', $domain_name) === 1;
}

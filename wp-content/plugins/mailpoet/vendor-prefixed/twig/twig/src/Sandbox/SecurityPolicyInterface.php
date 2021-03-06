<?php
namespace MailPoetVendor\Twig\Sandbox;
if (!defined('ABSPATH')) exit;
interface SecurityPolicyInterface
{
 public function checkSecurity($tags, $filters, $functions);
 public function checkMethodAllowed($obj, $method);
 public function checkPropertyAllowed($obj, $method);
}
\class_alias('MailPoetVendor\\Twig\\Sandbox\\SecurityPolicyInterface', 'MailPoetVendor\\Twig_Sandbox_SecurityPolicyInterface');

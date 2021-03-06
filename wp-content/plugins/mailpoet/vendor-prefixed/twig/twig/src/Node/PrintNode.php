<?php
namespace MailPoetVendor\Twig\Node;
if (!defined('ABSPATH')) exit;
use MailPoetVendor\Twig\Compiler;
use MailPoetVendor\Twig\Node\Expression\AbstractExpression;
class PrintNode extends Node implements NodeOutputInterface
{
 public function __construct(AbstractExpression $expr, int $lineno, string $tag = null)
 {
 parent::__construct(['expr' => $expr], [], $lineno, $tag);
 }
 public function compile(Compiler $compiler)
 {
 $compiler->addDebugInfo($this)->write('echo ')->subcompile($this->getNode('expr'))->raw(";\n");
 }
}
\class_alias('MailPoetVendor\\Twig\\Node\\PrintNode', 'MailPoetVendor\\Twig_Node_Print');

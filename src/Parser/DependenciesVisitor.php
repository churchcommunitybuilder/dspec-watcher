<?php

namespace DKoehn\DSpec\Parser;

use PhpParser\NodeVisitorAbstract;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;

class DependenciesVisitor extends NodeVisitorAbstract
{
    protected $adt;

    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Stmt\GroupUse) {
            $prefix = $node->prefix->toString();
            foreach ($node->uses as $use) {
                $this->adt->addDependency($prefix . "\\" . $use->name->toString());
            }
        } elseif ($node instanceof Node\Stmt\Use_) {
            foreach ($node->uses as $use) {
                /** @var Node\Stmt\UseUse $use */
                $this->adt->addDependency($use->name->toString());
            }
        } elseif ($node instanceof Node\Param) {
            if ($node->type instanceof Node\Name) {
                $this->adt->addDependency($node->type->toString());
            }
        } elseif ($node instanceof Name\FullyQualified) {
            $this->adt->addDependency($node->toString());
        } elseif ($node instanceof Stmt\Class_) {
            if ($node->name !== null) {
                $this->adt->setName($node->name->toString());
            }
        } elseif ($node instanceof Stmt\Namespace_) {
            $this->adt->setNamespace($node->name->toString());
        }
    }

    public function setAdt(Adt $adt)
    {
        $this->adt = $adt;
    }
}
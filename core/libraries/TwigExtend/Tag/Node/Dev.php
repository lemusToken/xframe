<?php
/**
 * Non production environment
 *
 * @author xule
 */

namespace Libs\TwigExtend\Tag\Node;

class Dev extends \Twig_Node {

    public function __construct(\Twig_NodeInterface $body, $lineno, $tag = 'dev') {
        parent::__construct(array('body' => $body), array(), $lineno, $tag);
    }

    public function compile(\Twig_Compiler $compiler) {
        $compiler
            ->addDebugInfo($this)
            ->write("if(\$context['ENV']!=='production'){\n")
            ->subcompile($this->getNode('body'))
            ->write("}\n")
        ;
    }
}

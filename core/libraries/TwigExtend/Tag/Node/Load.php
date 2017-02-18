<?php
/**
 * production environment
 *
 * @author xule
 */

namespace Libs\TwigExtend\Tag\Node;

class Load extends \Twig_Node {

    public function __construct($router,$file,\Twig_NodeInterface $body, $lineno, $tag = 'load') {
        parent::__construct(array('body' => $body), array(), $lineno, $tag);
        $this->setAttribute('router', $router);
        $this->setAttribute('file', $file);
    }

    public function compile(\Twig_Compiler $compiler) {
        $bodyNode = $this->getNode('body');
        $router = $this->getAttribute('router');
        $key = str_replace('/','_',$router);

        $compiler
            ->addDebugInfo($this)
            ->write("\$_contextSource_$key = \$context;")
            ->write("\$context = twig_escape_filter(\$this->env, call_user_func_array(\$this->env->getFunction('_loadData')->getCallable(), array('$router')), \"html\", null, true);\n")
            ->subcompile($bodyNode)
            ->write("\$context = \$_contextSource_$key;")
            ->write("unset(\$_contextSource_$key);\$_contextSource_$key = null;");
    }
}

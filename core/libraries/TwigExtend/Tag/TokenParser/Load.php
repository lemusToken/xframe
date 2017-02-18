<?php
/**
 * production environment
 *
 * <pre>
 *  {% pro %}
 *  {% endpro %}
 * </pre>
 * @author xule
 */

namespace Libs\TwigExtend\Tag\TokenParser;
use Libs\TwigExtend\Tag\Node\Load as NodeLoad;

class Load extends \Twig_TokenParser {

    public function parse(\Twig_Token $token) {
        $lineno = $token->getLine();
        $stream = $this->parser->getStream();
        $expr = $this->parser->getExpressionParser()->parseExpression();

        $router = $expr->getAttribute('value');
        $file = $stream->next()->getValue();
        $file = $file?:'/'.$stream->getFilename();

        $body = $this->parser->subparse(array($this, 'decideBlockEnd'), true);
        $stream->expect(\Twig_Token::BLOCK_END_TYPE);

        return new NodeLoad($router,$file,$body, $lineno, $this->getTag());
    }

    public function decideBlockEnd(\Twig_Token $token) {
        return $token->test('endload');
    }

    public function getTag() {
        return 'load';
    }
}

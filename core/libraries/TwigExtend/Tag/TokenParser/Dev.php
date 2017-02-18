<?php
/**
 * Non production environment
 *
 * <pre>
 *  {% dev %}
 *  {% enddev %}
 * </pre>
 */

namespace Libs\TwigExtend\Tag\TokenParser;
use Libs\TwigExtend\Tag\Node\Dev as NodeDev;

class Dev extends \Twig_TokenParser {

    public function parse(\Twig_Token $token) {
        $lineno = $token->getLine();

        $this->parser->getStream()->expect(\Twig_Token::BLOCK_END_TYPE);
        $body = $this->parser->subparse(array($this, 'decideBlockEnd'), true);
        $this->parser->getStream()->expect(\Twig_Token::BLOCK_END_TYPE);

        return new NodeDev($body, $lineno, $this->getTag());
    }

    public function decideBlockEnd(\Twig_Token $token) {
        return $token->test('enddev');
    }

    public function getTag() {
        return 'dev';
    }
}

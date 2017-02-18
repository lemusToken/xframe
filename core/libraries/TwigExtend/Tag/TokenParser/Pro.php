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
use Libs\TwigExtend\Tag\Node\Pro as NodePro;

class Pro extends \Twig_TokenParser {

    public function parse(\Twig_Token $token) {
        $lineno = $token->getLine();

        $this->parser->getStream()->expect(\Twig_Token::BLOCK_END_TYPE);
        $body = $this->parser->subparse(array($this, 'decideBlockEnd'), true);
        $this->parser->getStream()->expect(\Twig_Token::BLOCK_END_TYPE);

        return new NodePro($body, $lineno, $this->getTag());
    }

    public function decideBlockEnd(\Twig_Token $token) {
        return $token->test('endpro');
    }

    public function getTag() {
        return 'pro';
    }
}

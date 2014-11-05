<?php

require_once 'vendor/autoload.php';

$loader = new Twig_Loader_Filesystem('templates');
$twig = new Twig_Environment($loader, array(
    'cache' => 'cache',
    'debug' => true
));

class SeoPresenter
{
    public function updateSeoPage($seoPage)
    {
        $this->seoPage = $seoPage;
    }

    public function render()
    {
        echo '<title>'.$this->seoPage->title.'</title>';
    }

}

$presenter = new SeoPresenter();

class UpdateSeoPageNodeVisitor implements Twig_NodeVisitorInterface
{
    public function enterNode(Twig_NodeInterface $node, Twig_Environment $env)
    {
        if ($node instanceof UpdateSeoPageNode) {
            echo $node->compile($env->getCompiler());
            // var_dump($node->getAttribute('value'));
            // exit;
        }
        echo 'ENTER:'.$node->getNodeTag();
        return $node;
    }

    public function leaveNode(Twig_NodeInterface $node, Twig_Environment $env)
    {
        echo 'LEAVE:'.$node->getNodeTag();
        return $node;
    }

    public function getPriority()
    {
        return 0;
    }

}

class UpdateSeoPageNode extends \Twig_Node
{
    public function __construct($value, $lineno = 0, $tag = null)
    {
        parent::__construct(array('value' => $value), array(), array(), $lineno, $tag);
    }

    public function compile(\Twig_Compiler $compiler)
    {
        $compiler
            ->addDebugInfo($this)
            ->write("\$this->env->getExtension('update_seo_page_extension')->updateSeoPage(")
            ->subcompile($this->getNode('value'))
            ->raw(");\n")
        ;
    }
}

class UpdateSeoPageTokenParser extends \Twig_TokenParser
{
    public function parse(\Twig_Token $token)
    {
        $stream = $this->parser->getStream();
        $value = $this->parser->getExpressionParser()->parseMultitargetExpression();
        $stream->expect(\Twig_Token::BLOCK_END_TYPE);

        return new UpdateSeoPageNode($value, $token->getLine(), $this->getTag());
    }

    public function getTag()
    {
        return 'update_seo_page';
    }
}

class UpdateSeoPageExtension implements Twig_ExtensionInterface
{
    public function __construct($seoPresenter)
    {
        $this->seoPresenter = $seoPresenter;
    }

    public function updateSeoPage($seoPage)
    {
        $this->seoPresenter->updateSeoPage($seoPage);
    }

    public function initRuntime(Twig_Environment $environment)
    {
    }

    public function getTokenParsers()
    {
        return array(
            new UpdateSeoPageTokenParser()
        );
    }

    public function getNodeVisitors()
    {
        return array(
            new UpdateSeoPageNodeVisitor()
        );
    }

    public function getFilters()
    {
        return array();
    }

    public function getTests()
    {
        return array();
    }

    public function getFunctions()
    {
        return array();
    }

    public function getOperators()
    {
        return array();
    }

    public function getGlobals()
    {
        return array();
    }

    public function getName()
    {
        return 'update_seo_page_extension';
    }

}

$seoPresenter = new SeoPresenter();
$twig->addExtension(new UpdateSeoPageExtension($seoPresenter));

echo $twig->render('test.html.twig', array(
    'seo_presenter' => $seoPresenter,
    'seo_page' => (object) array('title' => 'Ben is great'),
    'name' => 'ben'
));

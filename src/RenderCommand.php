<?php
namespace wn;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;

class RenderCommand extends  Command {
    protected $twig;

    public function __construct($twig){
        parent::__construct();
        $this->twig = $twig;
    }

    public function configure(){
        $this->setName('render')
            ->setDescription('Render a snippet')
            ->addArgument('name', InputArgument::REQUIRED, 'The snippet name')
            ->addArgument('data', InputArgument::IS_ARRAY, 'Data to pass to the snippet');
    }

    public function execute(InputInterface $in, OutputInterface $out){
        $name = $in->getArgument('name');
        if(':' == $name[0] || ':' == $name[strlen($name) - 1])
            throw new Exception("The Snippet name '{$name}' is not valid !");
        $template = 'default';
        $index = strpos($name, ':');
        if(false !== $index){
            $template = substr($name, 0, $index);
            $name = substr($name, $index + 1);
        }
        if( ! is_file(SNIPPETS_DIR . "/{$name}/{$template}.twig"))
            $template = 'default';
        $data = $in->getArgument('data');
        if(is_file(SNIPPETS_DIR . "/{$name}/format.json")){
            $format = json_decode(file_get_contents( SNIPPETS_DIR . '/' . $name . '/format.json'));
            if(false == $format)
                throw new \Exception("Invalid JSON file: " . SNIPPETS_DIR . "/{$name}/format.json");
            $parser = new ArgumentsParser($format);
            $data = $parser->parse($data)->get();
        }
        $out->write($this->twig->render("{$name}/{$template}.twig", 
            ['data' => $data])
        );
    }
}
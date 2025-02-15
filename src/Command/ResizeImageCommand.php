<?php

namespace ImageResizer\Command;

use ImageResizer\Factory\ParserFactory;
use ImageResizer\Interface\ParserInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use ImageResizer\Service\ConfigLoader;
use ImageResizer\Service\ImageResizer;

class ResizeImageCommand extends Command
{
    protected static $defaultName = 'image:resize';

    protected function configure()
    {
        $this
            ->setDescription('Resize an image based on XML configuration')
            ->addArgument('image', InputArgument::REQUIRED, 'The image file to resize')
            ->addOption('xml', null, InputOption::VALUE_REQUIRED, 'Path to the XML config file')
            ->addOption('size', null, InputOption::VALUE_REQUIRED, 'Size preset to use')
            ->addOption('parser', null, InputOption::VALUE_OPTIONAL, 'The parser to use. Defaults to SimpleXmlParser', 'SimpleXmlParser');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $image = $input->getArgument('image');
        $xmlPath = $input->getOption('xml');
        $size = $input->getOption('size');
        $parserString = $input->getOption('parser');

        if (!$xmlPath || !$size) {
            $output->writeln('<error>You must specify --xml and --size options.</error>');
            return Command::INVALID;
        }

        $parser = $this->getParser($parserString);
        $config = new ConfigLoader($parser, $xmlPath);
        $imageResizer = new ImageResizer($config);
        $path = $imageResizer->resize($image, $size);
        die;
        $output->writeln("<info>Image resized and saved at: $path</info>");

        return Command::SUCCESS;
    }

    private function getParser(string $parserString): ParserInterface
    {
        $factory = new ParserFactory();
        return $factory->createParser($parserString);
    }
}
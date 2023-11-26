<?php


namespace App\Exporter;


use App\Entity\Element;
use Doctrine\Inflector\InflectorFactory;
use Twig\Environment;

class PythonModelExporter implements ExporterInterface
{
    
    /**
     * @var Environment
     */
    protected $twig;
    
    /**
     * PythonModelExporter constructor.
     *
     * @param Environment $twig
     */
    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }
    
    
    /**
     * @inheritDoc
     */
    public function export(
        \App\Entity\ParsingInstance $parsingInstance,
        $path
    )
    {
        $elements = $parsingInstance->getElements()->toArray();
        // filter elements to find the root element
        $elements = array_filter($elements, function (\App\Entity\Element $element) {
            return $element->getType() == \ElementTypeEnum::TYPE_OBJECT;
        });
        
        $exportName = sprintf("json-to-class-instance-%s.py", $parsingInstance->getId());
        $exportDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $exportName;
        if (!is_dir($exportDir)) {
            mkdir($exportDir);
        }
        
        foreach ($elements as $element) {
            $content = $this->exportElement($element);
            $relPath = $this->getObjectPath($element);
            $finalPath = $exportDir . DIRECTORY_SEPARATOR . $relPath;
            $finalDir = dirname($finalPath);
            $initPath = $finalDir . DIRECTORY_SEPARATOR . '__init__.py';
            if (!is_dir($finalDir)) {
                mkdir($finalDir, 0777, true);
            }
            if (!file_exists($initPath)) {
                touch($initPath);
            }
            file_put_contents($finalPath, $content);
        }
        
        $zip = new \ZipArchive();
        $zip->open($path, \ZipArchive::CREATE);
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($exportDir),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );
        foreach ($files as $name => $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($exportDir) + 1);
                $zip->addFile($filePath, $relativePath);
            }
        }
        $zip->close();
        
        exec("rm -rf $exportDir");
    }
    
    private function exportElement(Element $element)
    {
        $fields = [];
        $imports = [];
        foreach ($element->getChildren() as $child) {
            $field = [
                'name'     => $child->getName(),
                'type'     => $this->getType($child),
                'nullable' => $child->isNullable(),
            ];
            $fields[] = $field;
            
            $import = $this->getObjectImport($child);
            if (!in_array($import, $imports)) {
                $imports[] = $import;
            }
        }
        
        $imports = array_filter($imports);
        
        // filter empty type fields
        $fields = array_filter($fields, function ($field) {
            return $field['type'] != null;
        });
        
        $classContent = $this->twig->render('Exporter/python_model/class.py.twig', [
            'class_name' => $this->getClassName($element),
            'fields'     => $fields,
            'imports'    => $imports,
        ]);
        
        return $classContent;
    }
    
    private function getObjectImport(Element $element)
    {
        $allowedTypes = [
            \ElementTypeEnum::TYPE_OBJECT,
            \ElementTypeEnum::TYPE_ARRAY,
        ];
        if (!in_array($element->getType(), $allowedTypes)) {
            return null;
        }
        if ($element->getType() == \ElementTypeEnum::TYPE_ARRAY) {
            $element = $element->getChildren()->first();
        }
        $path = $this->getObjectPath($element);
        $path = rtrim($path, '.py');
        $path = str_replace(DIRECTORY_SEPARATOR, '.', $path);
        $className = $this->getClassName($element);
        return sprintf("from %s import %s", $path, $className);
    }
    
    private function getObjectPath(Element $element)
    {
        $path = [];
        $path[] = $element->getName();
        $parent = $element->getParent();
        while ($parent) {
            $path[] = $parent->getName();
            $parent = $parent->getParent();
        }
        $path = array_reverse($path);
        $path = array_map(function ($item) {
            return InflectorFactory::create()->build()->classify($item);
        }, $path);
        $path = implode(DIRECTORY_SEPARATOR, $path);
        $path .= '.py';
        return $path;
    }
    
    private function getClassName(Element $element)
    {
        return InflectorFactory::create()->build()->classify($element->getName());
    }
    
    private function getType(Element $element)
    {
        switch ($element->getType()) {
            case \ElementTypeEnum::TYPE_OBJECT:
                $type = $this->getClassName($element);
                break;
            case \ElementTypeEnum::TYPE_ARRAY:
                $type = sprintf('List[%s]', $this->getType($element->getChildren()->first()));
                break;
            case \ElementTypeEnum::TYPE_STRING:
                $type = 'str';
                break;
            case \ElementTypeEnum::TYPE_INTEGER:
                $type = 'int';
                break;
            case \ElementTypeEnum::TYPE_FLOAT:
                $type = 'float';
                break;
            case \ElementTypeEnum::TYPE_BOOLEAN:
                $type = 'bool';
                break;
            default:
                $type = null;
                break;
        }
        
        if ($element->isNullable()) {
            $type = sprintf('Optional[%s]', $type);
        }
        
        return $type;
    }
}
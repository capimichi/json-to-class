<?php


namespace App\Exporter;


use App\Entity\Element;
use App\Enum\ElementTypeEnum;
use App\Enum\PythonTypeEnum;
use App\EnumElementTypeEnum;
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
     * @param \App\Entity\ParsingInstance $parsingInstance
     * @param array                       $options
     *
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function export(\App\Entity\ParsingInstance $parsingInstance, $options = [])
    {
        $prefix = $options['prefix'] ?? '';
        $elements = $this->getElements($parsingInstance);
        $exportName = sprintf("json-to-class-instance-%s-%s.zip", $parsingInstance->getId(), date('YmdHis'));
        $exportDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $exportName;
        $path = $exportDir . DIRECTORY_SEPARATOR . $exportName;
        
        $this->createExportDirectory($exportDir);
        $dropPaths = $this->createFiles($elements, $exportDir, $prefix);
        $this->createZipArchive($path, $exportDir);
        $this->cleanup($dropPaths);
        
        return $path;
    }
    
    /**
     * @param $parsingInstance
     *
     * @return array
     */
    protected function getElements($parsingInstance)
    {
        $elements = $parsingInstance->getElements()->toArray();
        return array_filter($elements, function (\App\Entity\Element $element) {
            return $element->getType() == ElementTypeEnum::TYPE_OBJECT;
        });
    }
    
    /**
     * Creates the export directory.
     *
     * @param string $exportDir The path of the export directory.
     *
     * @return void
     */
    protected function createExportDirectory($exportDir)
    {
        if (!is_dir($exportDir)) {
            mkdir($exportDir);
        }
    }
    
    /**
     * @param $elements
     * @param $exportDir
     * @param $prefix
     *
     * @return array
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    protected function createFiles($elements, $exportDir, $prefix)
    {
        $dropPaths = [];
        foreach ($elements as $element) {
            $content = $this->exportElement($element, $prefix);
            $relPath = $this->getObjectPath($element, $prefix);
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
            $dropPaths[] = $finalPath;
        }
        return $dropPaths;
    }
    
    /**
     * Creates a zip archive.
     *
     * @param string $path      The path of the zip archive.
     * @param string $exportDir The directory to be exported.
     *
     * @return void
     */
    protected function createZipArchive($path, $exportDir)
    {
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
    }
    
    /**
     * Cleans up the given drop paths.
     *
     * @param array $dropPaths The drop paths to be cleaned up.
     *
     * @return void
     */
    protected function cleanup($dropPaths)
    {
        foreach ($dropPaths as $dropPath) {
            unlink($dropPath);
        }
    }
    
    /**
     * @param Element $element
     * @param string  $prefix
     *
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    protected function exportElement(Element $element, $prefix = '')
    {
        $fieldsAndImports = $this->getFieldsAndImports($element, $prefix);
        $fields = $this->filterAndSortFields($fieldsAndImports['fields']);
        $imports = $fieldsAndImports['imports'];
        return $this->renderClassContent($element, $fields, $imports);
    }
    
    /**
     * @param Element $element
     * @param         $prefix
     *
     * @return array
     */
    protected function getFieldsAndImports(Element $element, $prefix)
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
            
            $import = $this->getObjectImport($child, $prefix);
            if ($import && !in_array($import, $imports)) {
                $imports[] = $import;
            }
        }
        
        $imports = array_filter($imports);
        
        return ['fields' => $fields, 'imports' => $imports];
    }
    
    /**
     * @param $fields
     *
     * @return array
     */
    protected function filterAndSortFields($fields)
    {
        // filter empty type fields
        $fields = array_filter($fields, function ($field) {
            return $field['type'] != null;
        });
        
        // sort fields by nullable last
        usort($fields, function ($a, $b) {
            if ($a['nullable'] == $b['nullable']) {
                return 0;
            }
            return $a['nullable'] ? 1 : -1;
        });
        
        return $fields;
    }
    
    /**
     * @param $element
     * @param $fields
     * @param $imports
     *
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    protected function renderClassContent($element, $fields, $imports)
    {
        $classContent = $this->twig->render($this->getClassTemplatePath(), [
            'class_name' => $this->getClassName($element),
            'fields'     => $fields,
            'imports'    => $imports,
        ]);
        
        return $classContent;
    }
    
    protected function getClassTemplatePath()
    {
        return 'Exporter/python_model/class.py.twig';
    }
    
    /**
     * @param Element $element
     *
     * @return string
     */
    protected function getObjectImport(Element $element, $prefix = "")
    {
        $allowedTypes = [
            ElementTypeEnum::TYPE_OBJECT,
            ElementTypeEnum::TYPE_DICT,
            ElementTypeEnum::TYPE_ARRAY,
        ];
        if (!in_array($element->getType(), $allowedTypes)) {
            return null;
        }
        if (
            $element->getType() == ElementTypeEnum::TYPE_ARRAY
            || $element->getType() == ElementTypeEnum::TYPE_DICT
        ) {
            $child = $element->getChildren()->first();
            if (!$child) {
                return null;
            }
            $element = $element->getChildren()->first();
        }
        $path = $this->getObjectPath($element);
        $path = rtrim($path, '.py');
        $path = str_replace(DIRECTORY_SEPARATOR, '.', $path);
        $className = $this->getClassName($element);
        $prefixPart = $prefix ? $prefix . '.' : '';
        return sprintf("from %s%s import %s", $prefixPart, $path, $className);
    }
    
    /**
     * @param Element $element
     *
     * @return string
     */
    protected function getObjectPath(Element $element, $prefix = "")
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
        if ($prefix) {
            $prefixDir = str_replace('.', DIRECTORY_SEPARATOR, $prefix);
            $path = $prefixDir . DIRECTORY_SEPARATOR . $path;
        }
        $path .= '.py';
        return $path;
    }
    
    /**
     * @param Element $element
     *
     * @return string
     */
    protected function getClassName(Element $element)
    {
        return InflectorFactory::create()->build()->classify($element->getName());
    }
    
    /**
     * @param Element $element
     *
     * @return string
     */
    protected function getType(Element $element)
    {
        switch ($element->getType()) {
            case ElementTypeEnum::TYPE_OBJECT:
                $type = $this->getClassName($element);
                break;
            case ElementTypeEnum::TYPE_ARRAY:
                $child = $element->getChildren()->first();
                if (!$child) {
                    $type = null;
                } else {
                    $type = sprintf(PythonTypeEnum::TYPE_LIST . '[%s]', $this->getType($child));
                }
                break;
            case ElementTypeEnum::TYPE_DICT:
                $child = $element->getChildren()->first();
                if (!$child) {
                    $type = null;
                } else {
                    $type = sprintf(PythonTypeEnum::TYPE_DICT . '[%s, %s]', PythonTypeEnum::TYPE_STRING, $this->getType($child));
                }
                break;
            case ElementTypeEnum::TYPE_STRING:
                $type = PythonTypeEnum::TYPE_STRING;
                break;
            case ElementTypeEnum::TYPE_INTEGER:
                $type = PythonTypeEnum::TYPE_INTEGER;
                break;
            case ElementTypeEnum::TYPE_FLOAT:
                $type = PythonTypeEnum::TYPE_FLOAT;
                break;
            case ElementTypeEnum::TYPE_BOOLEAN:
                $type = PythonTypeEnum::TYPE_BOOLEAN;
                break;
            default:
                $type = PythonTypeEnum::TYPE_ANY;
                break;
        }
        
        if ($element->isNullable()) {
            $type = sprintf(PythonTypeEnum::TYPE_OPTIONAL . '[%s]', $type);
        }
        
        return $type;
    }
}
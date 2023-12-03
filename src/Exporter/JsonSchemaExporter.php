<?php


namespace App\Exporter;


use App\Entity\Element;

class JsonSchemaExporter implements ExporterInterface
{
    
    /**
     * @inheritDoc
     */
    public function export(
        \App\Entity\ParsingInstance $parsingInstance
    )
    {
        $elements = $parsingInstance->getElements()->toArray();
        // filter elements to find the root element
        $elements = array_filter($elements, function (\App\Entity\Element $element) {
            return $element->getType() == \ElementTypeEnum::TYPE_OBJECT && $element->getParent() == null;
        });
        if (count($elements) != 1) {
            throw new \Exception('There should be only one root element');
        }
        $rootElement = array_shift($elements);
        
        $data = [
            '$schema' => 'http://json-schema.org/draft-04/schema#',
        ];
        $data = array_merge($data, $this->exportElement($rootElement));
        
        $json = json_encode($data, JSON_PRETTY_PRINT);
        
        $name = sprintf("json-schema-%s-%s.json", $parsingInstance->getId(), date('YmdHis'));
        $path = $exportDir . DIRECTORY_SEPARATOR . $name;
        
        file_put_contents($path, $json);
        
        return $path;
    }
    
    private function exportElement(Element $element)
    {
        $data = [];
        switch ($element->getType()) {
            case \ElementTypeEnum::TYPE_OBJECT:
                $data = [
                    'type'       => 'object',
                    'properties' => [],
                    'required'   => [],
                ];
                $children = $element->getChildren();
                /** @var Element $child */
                foreach ($children as $child) {
                    $data['properties'][$child->getName()] = $this->exportElement($child);
                    if (!$child->isNullable()) {
                        $data['required'][] = $child->getName();
                    }
                }
                break;
            case \ElementTypeEnum::TYPE_ARRAY:
                $data = [
                    'type' => 'array',
                ];
                $children = $element->getChildren();
                if (count($children)) {
                    $data['items'] = $this->exportElement($children[0]);
                }
                break;
            case \ElementTypeEnum::TYPE_STRING:
                $data = [
                    'type' => 'string',
                ];
                break;
            case \ElementTypeEnum::TYPE_INTEGER:
                $data = [
                    'type' => 'integer',
                ];
                break;
            case \ElementTypeEnum::TYPE_BOOLEAN:
                $data = [
                    'type' => 'boolean',
                ];
                break;
        }
        
        return $data;
    }
}
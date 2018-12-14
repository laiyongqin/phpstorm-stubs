<?php
declare(strict_types=1);

namespace StubTests\Model;

use PhpParser\Node\Param;
use ReflectionParameter;
use stdClass;

class PHPParameter extends BasePHPElement
{
    public $type = '';
    public $is_vararg;
    public $is_passed_by_ref;

    /**
     * @param ReflectionParameter $parameter
     * @return $this
     */
    public function readObjectFromReflection($parameter): self
    {
        $this->name = $parameter->name;
        if (!empty($parameter->getType())) {
            $this->type = $parameter->getType()->getName();
        }
        $this->is_vararg = $parameter->isVariadic();
        $this->is_passed_by_ref = $parameter->isPassedByReference();
        return $this;
    }

    /**
     * @param Param $node
     * @return $this
     */
    public function readObjectFromStubNode($node): self
    {
        $this->name = $node->var->name;
        if ($node->type !== null) {
            if (empty($node->type->name)) {
                if (!empty($node->type->parts)) {
                    $this->type = $node->type->parts[0];
                }
            } else {
                $this->type = $node->type->name;
            }
        }
        $this->is_vararg = $node->variadic;
        $this->is_passed_by_ref = $node->byRef;
        return $this;
    }

    public function readStubProblems($jsonData): void
    {
        /**@var stdClass $parameter */
        foreach ($jsonData as $parameter) {
            if ($parameter->name === $this->name) {
                /**@var stdClass $problem */
                foreach ($parameter->problems as $problem) {
                    switch ($problem) {
                        case 'parameter type mismatch':
                            $this->relatedStubProblems[] = StubProblemType::PARAMETER_TYPE_MISMATCH;
                            break;
                        case 'parameter reference':
                            $this->relatedStubProblems[] = StubProblemType::PARAMETER_REFERENCE;
                            break;
                        case 'parameter vararg':
                            $this->relatedStubProblems[] = StubProblemType::PARAMETER_VARARG;
                            break;
                        default:
                            $this->relatedStubProblems[] = -1;
                            break;
                    }
                }
                return;
            }
        }
    }
}

<?php declare(strict_types = 1);

namespace PHPStan\Type\Generic;

use PHPStan\Type\Traits\UndecidedComparisonCompoundTypeTrait;
use PHPStan\Type\Type;

/**
 * @method GenericObjectType getBound()
 */
final class TemplateGenericObjectType extends GenericObjectType implements TemplateType
{

	use UndecidedComparisonCompoundTypeTrait;
	use TemplateTypeTrait;

	public function __construct(
		TemplateTypeScope $scope,
		TemplateTypeStrategy $templateTypeStrategy,
		TemplateTypeVariance $templateTypeVariance,
		string $name,
		GenericObjectType $bound
	)
	{
		parent::__construct($bound->getClassName(), $bound->getTypes());

		$this->scope = $scope;
		$this->strategy = $templateTypeStrategy;
		$this->variance = $templateTypeVariance;
		$this->name = $name;
		$this->bound = $bound;
	}

	public function toArgument(): TemplateType
	{
		return new self(
			$this->scope,
			new TemplateTypeArgumentStrategy(),
			$this->variance,
			$this->name,
			TemplateTypeHelper::toArgument($this->getBound())
		);
	}

	public function traverse(callable $cb): Type
	{
		$newBound = $cb($this->getBound());
		if ($this->getBound() !== $newBound && $newBound instanceof GenericObjectType) {
			return new self(
				$this->scope,
				$this->strategy,
				$this->variance,
				$this->name,
				$newBound
			);
		}

		return $this;
	}

	protected function recreate(string $className, array $types, ?Type $subtractedType): GenericObjectType
	{
		return new self(
			$this->scope,
			$this->strategy,
			$this->variance,
			$this->name,
			$this->getBound()
		);
	}

	/**
	 * @param mixed[] $properties
	 * @return Type
	 */
	public static function __set_state(array $properties): Type
	{
		return new self(
			$properties['scope'],
			$properties['strategy'],
			$properties['variance'],
			$properties['name'],
			$properties['bound']
		);
	}

}

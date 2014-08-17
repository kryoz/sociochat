<?php

namespace Core\Form;

use Core\Utils\WrongArgumentException;

class Form
{
	protected $rules;
	protected $rulesMessages;
	protected $errors;
	protected $input;
	protected $results;

	public function import(array $input)
	{
		$this->input = $input;
		return $this;
	}

	public function addRule($property, callable $rule, $message = null, $ruleName = null)
	{
		$ruleName = $ruleName ?: $property;
		if (isset($this->input[$property])) {
			$this->rules[$ruleName] = [
				'property' => $property,
				'rule' => $rule
			];
			$this->rulesMessages[$ruleName] = $message;
			return $this;
		}

		throw new WrongRuleNameException("Form.addRule : incorrect property name '$property'");
	}

	/**
	 * @return bool
	 */
	public function validate()
	{
		$this->errors = [];

		foreach ($this->rules as $ruleName => $ruleData) {
			$rule = $ruleData['rule'];
			$property = $ruleData['property'];

			// @TODO pass Form in callable
			if (!$result = $rule($this->input[$property])) {
				$this->errors[$ruleName] = $this->rulesMessages[$ruleName];
				break;
			}
			$this->results[$ruleName] = $result;
		}

		return !$this->hasErrors();
	}

	/**
	 * @return bool
	 */
	public function hasErrors()
	{
		return !empty($this->errors);
	}

	public function getErrors()
	{
		return $this->errors;
	}

	/**
	 * @param $property
	 * @return mixed
	 */
	public function getErrMsg($property)
	{
		if (isset($this->errors[$property])) {
			return $this->errors[$property];
		}
	}

	/**
	 * @param $property
	 * @return mixed
	 */
	public function getResult($property)
	{
		if (isset($this->results[$property])) {
			return $this->results[$property];
		}
	}

	public function markWrong($property, $errMsg)
	{
		$this->errors[$property] = $errMsg;
	}

	public function getValue($property)
	{
		if (!isset($this->input[$property])) {
			throw new WrongArgumentException('Invalid property name '.$property);
		}

		return $this->input[$property];
	}
}
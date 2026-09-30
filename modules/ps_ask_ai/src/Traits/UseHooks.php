<?php

/**
 * Copyright (c) 2026 PrestaShop SA
 *
 * This file is part of the PrestaShop AskAI solution.
 * For license terms, see LICENSE.md in this module.
 */

declare(strict_types=1);

namespace PrestaShop\Module\PsAskAi\Traits;

trait UseHooks
{
    use Hooks\UseActionAdminControllerSetMedia;
    use Hooks\UseDisplayBackOfficeHeader;
    use Hooks\UseDisplayBackOfficeFooter;

    /**
     * @var array An array of method that can be called to register media in the actionAdminControllerSetMedia hook
     *
     * @see UseActionAdminControllerSetMedia
     */
    protected $adminControllerMediaMethods = [];

    /**
     * @var array An array of method that can be called to register media in the actionAdminLoginControllerSetMedia hook
     *
     * @see UseActionAdminLoginControllerSetMedia
     */
    protected $adminLoginControllerMediaMethods = [];

    /**
     * Try to call the "bootHookClassName" method on each hook class.
     *
     * @return void
     */
    protected function bootHooks(): void
    {
        foreach ($this->getTraitNames() as $traitName) {
            if (method_exists($this, "boot{$traitName}")) {
                $this->{"boot{$traitName}"}();
            }
        }
    }

    /**
     * Try to call the "hookClassNameExtraOperations" method on each hook class.
     *
     * @return void
     */
    protected function installHooks(): void
    {
        foreach ($this->getTraitNames() as $traitName) {
            $traitName = lcfirst($traitName);
            if (method_exists($this, "{$traitName}ExtraOperations")) {
                $this->{"{$traitName}ExtraOperations"}();
            }
        }
    }

    /**
     * Guess the hooks names by using the traits names. Remove the "Use" in the traits name.
     *
     * @return string[]
     */
    protected function getHooksNames(): array
    {
        return array_map(function ($trait) {
            return str_replace('Use', '', $trait);
        }, $this->getTraitNames());
    }

    /**
     * Parse all classes used by this trait, and extract them
     *
     * @return string[]
     */
    protected function getTraitNames(): array
    {
        $traits = [];
        // Retrieve all used classes and iterate
        foreach (class_uses(UseHooks::class) as $trait) {
            // Get only the class name eg. 'UseAdminControllerSetMedia'
            $traits[] = substr(strrchr($trait, '\\'), 1);
        }

        return $traits;
    }
}

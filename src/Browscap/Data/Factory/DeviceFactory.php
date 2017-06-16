<?php
/**
 * Copyright (c) 1998-2017 Browser Capabilities Project
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 * Refer to the LICENSE file distributed with this package.
 *
 * @category   Browscap
 * @copyright  1998-2017 Browser Capabilities Project
 * @license    MIT
 */

namespace Browscap\Data\Factory;

use Browscap\Data\Device;

/**
 * Class DeviceFactory
 *
 * @category   Browscap
 * @author     Thomas Müller <t_mueller_stolzenhain@yahoo.de>
 */
class DeviceFactory
{
    /**
     * Load a engines.json file and parse it into the platforms data array
     *
     * @param array  $deviceData
     * @param array  $json
     * @param string $deviceName
     *
     * @throws \RuntimeException     if the file does not exist or has invalid JSON
     * @return \Browscap\Data\Device
     */
    public function build(array $deviceData, array $json, $deviceName)
    {
        if (!isset($deviceData['properties'])) {
            $deviceData['properties'] = [];
        }

        if (!array_key_exists('standard', $deviceData)) {
            throw new \UnexpectedValueException(
                'the value for "standard" key is missing for device "' . $deviceName . '"'
            );
        }

        if (array_key_exists('inherits', $deviceData)) {
            $parentName = $deviceData['inherits'];

            if (!isset($json['devices'][$parentName])) {
                throw new \UnexpectedValueException(
                    'parent Device "' . $parentName . '" is missing for device "' . $deviceName . '"'
                );
            }

            $parentEngine     = $this->build($json['devices'][$parentName], $json, $parentName);
            $parentEngineData = $parentEngine->getProperties();

            $inheritedPlatformProperties = $deviceData['properties'];

            foreach ($inheritedPlatformProperties as $name => $value) {
                if (isset($parentEngineData[$name])
                    && $parentEngineData[$name] === $value
                ) {
                    throw new \UnexpectedValueException(
                        'the value for property "' . $name . '" has the same value in the keys "' . $deviceName
                        . '" and its parent "' . $deviceData['inherits'] . '"'
                    );
                }
            }

            $deviceData['properties'] = array_merge(
                $parentEngineData,
                $inheritedPlatformProperties
            );

            if (!$parentEngine->isStandard()) {
                $deviceData['standard'] = false;
            }
        }

        return new Device($deviceData['properties'], $deviceData['standard']);
    }
}

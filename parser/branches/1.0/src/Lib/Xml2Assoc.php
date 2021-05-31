<?php

declare(strict_types=1);

namespace Pollen\Parser\Lib;

use Exception;
use XMLReader;
use Pollen\Support\Arr;
use Throwable;

class Xml2Assoc
{

    /**
     * Optimization Enabled / Disabled
     *
     * @var bool
     */
    protected $bOptimize = false;

    /**
     * Method for loading XML Data from String
     *
     * @param string $sXml
     * @param bool $bOptimize
     *
     * @return array
     *
     * @throws Throwable
     */
    public function parseString(string $sXml, bool $bOptimize = false): array
    {
        $oXml = new XMLReader();
        $this->bOptimize = $bOptimize;
        try {
            $oXml::XML($sXml);

            return $this->parseXml($oXml);
        } catch (Throwable $e) {
            throw $e;
        }
    }

    /**
     * Method for loading Xml Data from file
     *
     * @param string $sXmlFilePath
     * @param bool $bOptimize
     *
     * @return array
     *
     * @throws Exception
     */
    public function parseFile(string $sXmlFilePath, $bOptimize = false)
    {
        $oXml = new XMLReader();
        $this->bOptimize = (bool)$bOptimize;


        $oXml->open($sXmlFilePath);

        // // Parse Xml and return result
        return $this->parseXml($oXml);
    }

    /**
     * XML Parser
     *
     * @param XMLReader $oXml
     *
     * @return array
     */
    protected function parseXml(XMLReader $oXml)
    {

        $aAssocXML = null;
        $iDc = -1;

        while ($oXml->read()) {
            if ($oXml->depth === 0) {
                continue;
            }

            switch ($oXml->nodeType) {

                case XMLReader::END_ELEMENT:
                    if ($this->bOptimize) {
                        $this->optXml($aAssocXML);
                    }

                    return $aAssocXML;

                case XMLReader::ELEMENT:

                    if (!isset($aAssocXML[$oXml->name])) {
                        if ($oXml->hasAttributes) {
                            $aAssocXML[$oXml->name][] = $oXml->isEmptyElement ? '' : $this->parseXML($oXml);
                        } else {
                            if ($oXml->isEmptyElement) {
                                $aAssocXML[$oXml->name] = '';
                            } else {
                                $aAssocXML[$oXml->name] = $this->parseXML($oXml);
                            }
                        }
                    } elseif (is_array($aAssocXML[$oXml->name])) {
                        if (!isset($aAssocXML[$oXml->name][0])) {
                            $temp = $aAssocXML[$oXml->name];
                            foreach ($temp as $sKey => $sValue) {
                                unset($aAssocXML[$oXml->name][$sKey]);
                            }
                            $aAssocXML[$oXml->name][] = $temp;
                        }

                        if ($oXml->hasAttributes) {
                            $aAssocXML[$oXml->name][] = $oXml->isEmptyElement ? '' : $this->parseXML($oXml);
                        } else {
                            if ($oXml->isEmptyElement) {
                                $aAssocXML[$oXml->name][] = '';
                            } else {
                                $aAssocXML[$oXml->name][] = $this->parseXML($oXml);
                            }
                        }
                    } else {
                        $mOldVar = $aAssocXML[$oXml->name];
                        $aAssocXML[$oXml->name] = [$mOldVar];
                        if ($oXml->hasAttributes) {
                            $aAssocXML[$oXml->name][] = $oXml->isEmptyElement ? '' : $this->parseXML($oXml);
                        } else {
                            if ($oXml->isEmptyElement) {
                                $aAssocXML[$oXml->name][] = '';
                            } else {
                                $aAssocXML[$oXml->name][] = $this->parseXML($oXml);
                            }
                        }
                    }

                    if ($oXml->hasAttributes) {
                        $mElement =& $aAssocXML[$oXml->name][count($aAssocXML[$oXml->name]) - 1];
                        while ($oXml->moveToNextAttribute()) {
                            $mElement[$oXml->name] = $oXml->value;
                        }
                    }
                    break;
                case XMLReader::TEXT:
                case XMLReader::CDATA:
                    $aAssocXML[++$iDc] = $oXml->value;

            }
        }

        return $aAssocXML;
    }

    /**
     * Method to optimize assoc tree.
     * ( Deleting 0 index when element
     *  have one attribute / value )
     *
     * @param array $mData
     */
    public function optXml(&$mData)
    {
        if (is_array($mData)) {
            if (isset($mData[0]) && (count($mData) == 1)) {
                $mData = $mData[0];
                if (is_array($mData)) {
                    foreach ($mData as &$aSub) {
                        $this->optXml($aSub);
                    }
                }
            } elseif (!Arr::isAssoc($mData)) {
                foreach ($mData as &$aSub) {
                    $this->optXml($aSub);
                }
            }
        }
    }

}
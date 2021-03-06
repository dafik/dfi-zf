<?php
/**
 * Created by IntelliJ IDEA.
 * User: dafi
 * Date: 16.04.17
 * Time: 13:40
 */

namespace Dfi\Iface\Model\Pbx;


use Dfi\Iface\Model;

interface AccountSip extends Model
{

    public function setMessage($message);

    public function getGuid();

    public function setDescription($description);

    public function setVariables($variables);

    public function setFile($getFile);

    public function setLine($getLine);

    public function getDdiNumber();

    public function getPassword();

}
<?

class Dfi_Controller_Action_Helper_Acl extends Zend_Controller_Action_Helper_Abstract
{

    /**
     * Acl
     *
     */
    private $acl;

    /**
     * Controller
     *
     * @var Controller_Base
     */
    private $controller;

    /**
     * roleName
     *
     * @var
     */
    private $roleName;
    private $roleId;

    private $aclConfig = array();


    public function __construct()
    {
        $this->acl = new Zend_Acl();

    }

    public function init($modelName)
    {
        parent::init();

        $this->setRoles($modelName);
        $this->setResources();
        $this->setPrivilages();
        $this->setAcl();
    }

    private function setRoles($modelName)
    {
        Zend_Auth::getInstance()->setStorage(new Dfi_Auth_Storage_Cookie($modelName));
        if (Zend_Auth::getInstance()->hasIdentity()) {

            $user = Zend_Auth::getInstance()->getIdentity();
            /* @var $user User */

            $this->roleName = $user->getRole()->getName();
            $this->roleId = $user->getRole()->getId();
            $this->acl->addRole(new Zend_Acl_Role($this->roleId));
        }

    }

    private function setResources()
    {
        //TODO from file


        $modules = ModuleQuery::create()->filterByTreeLevel(0, Criteria::GREATER_THAN)->find();
        //$this->acl->add(new Zend_Acl_Resource('index'));
        foreach ($modules as $module) {
            /* @var $module SysModule */
            $this->acl->add(new Zend_Acl_Resource($module->getId()));
        }
    }

    private function setPrivilages()
    {

        if (Zend_Auth::getInstance()->hasIdentity()) {
            //$privilages = AclQuery::create()->filterByRoleId($this->roleId)->find();
            $privileges = array();
            $userPrivileges = Dfi_Auth_Acl::getModulesIdsByRoleId($this->roleId);
            $userPrivileges = $this->checkResources($userPrivileges);
            //$userPrivilages = array();
            if ($userPrivileges) {
                $this->acl->allow($this->roleId, $userPrivileges);
            }

            $user = Zend_Auth::getInstance()->getIdentity();
            /* @var $user SysUser */
            if ($user->getId() == 6) {
                //$this->acl->allow($this->roleId); //all
            }

        }
    }

    private function checkResources($resources, $acl = null)
    {
        $acl = $this->acl;
        $filtered = array();
        foreach ($resources as $resource) {
            try {
                if ($acl->get($resource)) {
                    $filtered[] = $resource;
                }
            } catch (Exception $e) {
                $x = 1;
            }
        }
        return $filtered;
    }

    private function setAcl()
    {
        Zend_Registry::set('acl', $this->acl);
    }


}
<?php
/**
 * Created by PhpStorm.
 * User: sunny
 * Date: 2015/9/16
 * Time: 14:56
 */
namespace OC\Files\Mount;
use OCP\Files\Mount\IMountDatabase;


class MountDatabase implements IMountDatabase{

    /**
     * 获取mount表中所有mount信息
     * time:2015.9.16
     * by sunny
     * @return 所有mount信息
     */
    public function getAllMountPoints()
    {
        $query = \OC_DB::prepare('SELECT * FROM `*PREFIX*mount` ');
        $result = $query->execute();
        $res=$result->fetchAll();
        return $res;
    }
    /**
     * 获取对应用户名的mount信息
     * time:2015.9.16
     * by sunny
     * @return 某个mount信息
     */
    public function getMountPointByUserAndStorage($uid,$storage)
    {
        $query = \OC_DB::prepare('SELECT * FROM `*PREFIX*mount`  WHERE LOWER(`name`) = LOWER(?) AND `storage_name`=?');
        $result = $query->execute(array($uid,$storage));
        $res=$result->fetchRow();
        return $res;
    }
    /**
     * 获取对应用户名的mount信息
     * time:2015.9.16
     * by sunny
     * @return 某个mount信息
     */
    public function getMountPointByUser($uid)
    {
        $query = \OC_DB::prepare('SELECT * FROM `*PREFIX*mount`  WHERE LOWER(`name`) = LOWER(?)');
        $result = $query->execute(array($uid));
        $res=$result->fetchRow();
        return $res;
    }
    /**
     * 插入mount数据库
     * time:2015.9.16
     * by sunny
     * @return true or false
     */
    public function insertMountPoint($mountpoint)
    {
        $name=key($mountpoint);

        $storage_name=key($mountpoint[$name]);

        $storage=$mountpoint[$name][$storage_name];

        $class=$storage['class'];

        $option=json_encode($storage['options']);

        $priority=$storage['priority'];

        $storage_id=$storage['storage_id'];

        $query = \OC_DB::prepare('INSERT INTO `*PREFIX*mount` ( `category`, `name`, `storage_name`, `class`, `options`, `priority`, `storage_id` ) VALUES( ?, ?, ?, ?, ?, ?, ? )');
        $result = $query->execute(array(0,$name, $storage_name,$class,$option,$priority,$storage_id));

        return $result ? true : false;
    }
    /**
     * 根据用户uid删除mount信息
     * time:2015.9.16
     * by sunny
     * @return 删除某个mount信息
     */
    public function deleteMountPoint($uid,$storage)
    {
        $query = \OC_DB::prepare('DELETE FROM `*PREFIX*mount` WHERE `name` = ? AND `storage_name` = ?');
        $result = $query->execute(array($uid,$storage));

        return $result ? true : false;
    }
}

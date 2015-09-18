<?php
/**
 * Created by PhpStorm.
 * User: sunny
 * Date: 2015/9/16
 * Time: 14:58
 * 操作mount数据库信息的接口
 */
namespace OCP\Files\Mount;

interface IMountDatabase{
    //获取所有的数据
    public function getAllMountPoints();

    //根据用户id以及storage_name获取mount数据
    public function getMountPointByUserAndStorage($uid,$storage);

    //根据用户id获取mount数据
    public function getMountPointByUser($uid);

    //根据传来的数据，将数据插入到数据库中
    public function insertMountPoint($mountpoint);

    //根据传来的数据，将数据删除出数据库
    public function deleteMountPoint($uid,$storage);
}

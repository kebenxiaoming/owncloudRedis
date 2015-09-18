<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2015/9/17
 * Time: 12:24
 */

$mount=new \OC\Files\Mount\MountDatabase();

$result=$mount->getAllMountPoints();
//$result=$mount->getMountPointByUserAndStorage('sunny','/$user/files/FTP');
$users=array();
foreach($result as $res) {
    $options=json_decode($res['options'],true);
    $arr=array($res['name']=>array(
        $res['storage_name']=>array(
        'class'=>$res['class'],
        'options'=>$options,
        'priority'=>$res['priority'],
        'storage_id'=>$res['storage_id']
    )
    )
    );
    $users=array_merge($users,$arr);
}
$mountpoints=array('user'=>$users);
print_r($mountpoints);
//$str='{
//        "admin": {
//            "\/$user\/files\/FTP": {
//                "class": "\\OC\\Files\\Storage\\FTP",
//                "options": {
//                    "host": "192.168.200.122",
//                    "user": "sunny",
//                    "password": "",
//                    "root": "1100000002",
//                    "secure": "false",
//                    "password_encrypted": "bng1Z3JqZHR5cHluZHUxa+YC\/PQLE9ksmn3TPM1MDiw="
//                },
//                "priority": 100,
//                "storage_id": "6"
//            }
//        }
//    }';
//$str2='{
//        "admin1": {
//            "\/$user\/files\/FTP": {
//                "class": "\\OC\\Files\\Storage\\FTP",
//                "options": {
//                    "host": "192.168.200.122",
//                    "user": "sunny",
//                    "password": "",
//                    "root": "1100000002",
//                    "secure": "false",
//                    "password_encrypted": "bng1Z3JqZHR5cHluZHUxa+YC\/PQLE9ksmn3TPM1MDiw="
//                },
//                "priority": 100,
//                "storage_id": "6"
//            }
//        }
//    }';
//$arr1=json_decode(stripslashes($str),true);
//$arr2=json_decode(stripslashes($str2),true);
//$arr=array_merge($arr1,$arr2);
//print_r($arr);
$str='{"group":{"admin":{"\/$user\/files\/FTP":{"class":"\\OC\\Files\\Storage\\FTP","options":{"host":"192.168.200.122","user":"sunny","password":"","root":"1100000002","secure":"false","password_encrypted":"bng1Z3JqZHR5cHluZHUxa+YC\/PQLE9ksmn3TPM1MDiw="},"priority":100,"storage_id":"6"}}}}';
print_r(json_decode(stripslashes($str),true));
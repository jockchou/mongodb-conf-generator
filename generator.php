 <?php
 
define("BASEDIR", str_replace("\\", "/", dirname(__FILE__)));

//帮定IP
define("BIND_IP", "127.0.0.1,192.168.200.2");

//配置服务器IP，IP个数与配置服务器个数保持相同
define("CFG_IP", "192.168.200.2,192.168.200.2,192.168.200.2");

//mongodb bin目录
define("BINPATH", "/usr/local/mongodb/bin");

//超始端口号
define("START_PORT", 40000);

//配置服务器个数
define("CFG_SVR_NUM", 3);

//分片个数
define("RS_NUM", 3);

//复制集节点数
define("RS_NODE_NUM", 3);

$cfgdb_port_group = array();

//创建配置服务器目录
function create_cfg_path() {
    echo "\n";
    
    for($i = 0; $i < CFG_SVR_NUM; $i++) {
        $cnf_dir = "./cfg/node$i/cnf";
        $dat_dir = "./cfg/node$i/data";
        $log_dir = "./cfg/node$i/log";
        
        if (!file_exists($cnf_dir)) mkdir($cnf_dir, 0755, true);
        if (!file_exists($dat_dir)) mkdir($dat_dir, 0755, true);
        if (!file_exists($log_dir)) mkdir($log_dir, 0755, true);
        
        write_cfg_cnf($cnf_dir, $i);
    }
    
    echo "\n";
}

//创建复制集目录
function create_rs_path() {
    for($i = 0; $i < RS_NUM; $i++) {
        for($j = 0; $j < RS_NODE_NUM; $j++) {
        
            $cnf_dir = "./rs$i/node$j/cnf";
            $dat_dir = "./rs$i/node$j/data";
            $log_dir = "./rs$i/node$j/log";
            
            if (!file_exists($cnf_dir)) mkdir($cnf_dir, 0755, true);
            if (!file_exists($dat_dir)) mkdir($dat_dir, 0755, true);
            if (!file_exists($log_dir)) mkdir($log_dir, 0755, true);
            
            
            write_rs_cnf($cnf_dir, $i, $j);
        }
        
        echo "\n";
    }
}

//创建路由服务器目录
function create_mongos_path() {
    
    $cnf_dir = "./mongos/cnf";
    $log_dir = "./mongos/log";
    
    if (!file_exists($cnf_dir)) mkdir($cnf_dir, 0755, true);
    if (!file_exists($log_dir)) mkdir($log_dir, 0755, true);
    
    write_mongos_cnf($cnf_dir);
    
    echo "\n";
}

//生成复制集配置文件
function write_rs_cnf($cnf_dir, $rs, $node) {

    $port = START_PORT + CFG_SVR_NUM + RS_NODE_NUM * $rs + $node;
    $rsName = "rs" . $rs;
    $nodeName = "node" . $node;
    
    $rs_tpl_file = "./mongod.conf.template";
    $handle = fopen($rs_tpl_file, "r");
    $contents = fread($handle, filesize($rs_tpl_file));
    $contents = str_replace("#basedir#", BASEDIR, $contents);
    $contents = str_replace("#rs#", $rsName, $contents);
    $contents = str_replace("#node#", $nodeName, $contents);
    $contents = str_replace("#port#", $port, $contents);
    $contents = str_replace("#bindIp#", BIND_IP, $contents);
    
    $fp = fopen($cnf_dir . "/mongod.conf", 'w,ccs=UTF-8');
    fwrite($fp, $contents);
    
    fclose($fp);
    fclose($handle);
    
    $shell = BINPATH . "/mongod --fork --config " . BASEDIR ."/$rsName/$nodeName/cnf/mongod.conf &";
    $sh_file = BASEDIR . "/$rsName/$nodeName/mongostart.sh";
    $shfp = fopen($sh_file, 'w,ccs=UTF-8');
    fwrite($shfp, "#!/bin/bash\n\n");
    fwrite($shfp, $shell);
    fclose($shfp);
    
    echo "$rsName->$nodeName->$port\n";
    
}

//生成配置服务器配置文件
function write_cfg_cnf($cnf_dir, $node) {
    global $cfgdb_port_group;
    
    $port = START_PORT + $node;
    $nodeName = "node" . $node;
    
    $cfg_tpl_file = "./cfg.conf.template";
    $handle = fopen($cfg_tpl_file, "r");
    $contents = fread($handle, filesize($cfg_tpl_file));
    $contents = str_replace("#basedir#", BASEDIR, $contents);
    $contents = str_replace("#node#", $nodeName, $contents);
    $contents = str_replace("#port#", $port, $contents);
    $contents = str_replace("#bindIp#", BIND_IP, $contents);
    
    array_push($cfgdb_port_group, $port);
    $fp = fopen($cnf_dir . "/cfg.conf", 'w,ccs=UTF-8');
    fwrite($fp, $contents);
    
    fclose($fp);
    fclose($handle);
    
    $shell = BINPATH . "/mongod --fork --config " . BASEDIR ."/cfg/{$nodeName}/cnf/cfg.conf &";
    $sh_file = BASEDIR . "/cfg/{$nodeName}/mongostart.sh";
    $shfp = fopen($sh_file, 'w,ccs=UTF-8');
    fwrite($shfp, "#!/bin/bash\n\n");
    fwrite($shfp, $shell);
    fclose($shfp);
    
    echo "cfg->$nodeName->$port\n";
}

//生成路由服务器配置文件
function write_mongos_cnf($cnf_dir) {
    global $cfgdb_port_group;

    $port = START_PORT + CFG_SVR_NUM + (RS_NUM * RS_NODE_NUM);
    
    $mongos_tpl_file = "./mongos.conf.template";
    $handle = fopen($mongos_tpl_file, "r");
    $contents = fread($handle, filesize($mongos_tpl_file));
    $contents = str_replace("#basedir#", BASEDIR, $contents);
    $contents = str_replace("#port#", $port, $contents);
    $contents = str_replace("#bindIp#", BIND_IP, $contents);
    
    $cfg_ip_group = explode(",", CFG_IP);
    $cfgdb_port_str = array();
    foreach($cfgdb_port_group as $n => $cfg_port) {
        if ($n < count($cfg_ip_group))
        array_push($cfgdb_port_str, $cfg_ip_group[$n] . ":" . $cfg_port);
    }
    $contents = str_replace("#configDB#", implode(",", $cfgdb_port_str), $contents);
     
    $fp = fopen($cnf_dir . "/mongos.conf", 'w,ccs=UTF-8');
    fwrite($fp, $contents);
    
    fclose($fp);
    fclose($handle);
    
    $shell = BINPATH . "/mongos --fork --config " . BASEDIR ."/mongos/cnf/mongos.conf &";
    $sh_file = BASEDIR . "/mongos/mongostart.sh";
    $shfp = fopen($sh_file, 'w,ccs=UTF-8');
    fwrite($shfp, "#!/bin/bash\n\n");
    fwrite($shfp, $shell);
    fclose($shfp);
    
    echo "mongos----->$port\n";
}

//入口函数
function main() {
    create_cfg_path();
    
    create_rs_path();
    
    create_mongos_path();
}

main();

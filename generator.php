 <?php
 
define("BASEDIR", str_replace("\\", "/", dirname(__FILE__)));

//帮定IP
define("BIND_IP", "127.0.0.1,192.168.100.10,192.168.100.11,192.168.100.12");

//mongodb bin目录
define("BINPATH", "/usr/local/mongodb/bin");

//超始端口号
define("START_PORT", 4000);

//配制服务器个数
define("CFG_SVR_NUM", 3);

//分片个数
define("RS_NUM", 3);

//复制集节点数
define("RS_NODE_NUM", 3);

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

function create_mongos_path() {
    
    $cnf_dir = "./mongos/cnf";
    $log_dir = "./mongos/log";
    
    if (!file_exists($cnf_dir)) mkdir($cnf_dir, 0755, true);
    if (!file_exists($log_dir)) mkdir($log_dir, 0755, true);
    
    write_mongos_cnf($cnf_dir);
    
    echo "\n";
}

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
    
    $fp = fopen($cnf_dir . "/mongod.conf", 'w');
    fwrite($fp, $contents);
    
    fclose($fp);
    fclose($handle);
    
    $shell = BINPATH . "/mongod --fork --config " . BASEDIR ."/$rsName/$nodeName/cnf/mongod.conf &";
    $sh_file = BASEDIR . "/$rsName/$nodeName/mongostart.sh";
    $shfp = fopen($sh_file, 'w');
    fwrite($shfp, "#!/bin/bash\n\n");
    fwrite($shfp, $shell);
    fclose($shfp);
    
    echo "$rsName->$nodeName->$port\n";
    
}

function write_cfg_cnf($cnf_dir, $node) {
    $port = START_PORT + $node;
    $nodeName = "node" . $node;
    
    $cfg_tpl_file = "./cfg.conf.template";
    $handle = fopen($cfg_tpl_file, "r");
    $contents = fread($handle, filesize($cfg_tpl_file));
    $contents = str_replace("#basedir#", BASEDIR, $contents);
    $contents = str_replace("#node#", $nodeName, $contents);
    $contents = str_replace("#port#", $port, $contents);
    $contents = str_replace("#bindIp#", BIND_IP, $contents);
    
    $fp = fopen($cnf_dir . "/cfg.conf", 'w');
    fwrite($fp, $contents);
    
    fclose($fp);
    fclose($handle);
    
    $shell = BINPATH . "/mongod --fork --config " . BASEDIR ."/cfg/{$nodeName}/cnf/cfg.conf &";
    $sh_file = BASEDIR . "/cfg/{$nodeName}/mongostart.sh";
    $shfp = fopen($sh_file, 'w');
    fwrite($shfp, "#!/bin/bash\n\n");
    fwrite($shfp, $shell);
    fclose($shfp);
    
    echo "cfg->$nodeName->$port\n";
}

function write_mongos_cnf($cnf_dir) {
    $port = START_PORT + CFG_SVR_NUM + (RS_NUM * RS_NODE_NUM);
    
    $mongos_tpl_file = "./mongos.conf.template";
    $handle = fopen($mongos_tpl_file, "r");
    $contents = fread($handle, filesize($mongos_tpl_file));
    $contents = str_replace("#basedir#", BASEDIR, $contents);
    $contents = str_replace("#port#", $port, $contents);
    $contents = str_replace("#bindIp#", BIND_IP, $contents);

    $fp = fopen($cnf_dir . "/mongos.conf", 'w');
    fwrite($fp, $contents);
    
    fclose($fp);
    fclose($handle);
    
    $shell = BINPATH . "/mongos --fork --config " . BASEDIR ."/mongos/cnf/mongos.conf &";
    $sh_file = BASEDIR . "/mongos/mongostart.sh";
    $shfp = fopen($sh_file, 'w');
    fwrite($shfp, "#!/bin/bash\n\n");
    fwrite($shfp, $shell);
    fclose($shfp);
    
    echo "mongos----->$port\n";
}

function main() {
    create_cfg_path();
    
    create_rs_path();
    
    create_mongos_path();
}

main();
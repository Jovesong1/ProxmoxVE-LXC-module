<?php
// Jovesong编
use app\common\logic\RunMap;
use app\common\model\HostModel;
use think\Db;

// 配置数据
function proxmoxlxc_MetaData(){
	return ['DisplayName'=>'proxmox-Lxc对接模块', 'APIVersion'=>'1.0', 'HelpDoc'=>'https://imzxw.cn/?p=144'];
}

// 测试链接
function proxmoxlxc_TestLink($params){
    
    // 构建URL
    
    // $url = $params['server_http_prefix']."://".$params['server_ip'].":".$params['port'];
    // return $url;
    
    $info = json_decode(proxmoxlxc_request($params,"/api2/json/nodes/".$params['hostname']."/status"),true);
    
    if($info==null || $info['data']==null){
        $result['status'] = 200;
		$result['data']['server_status'] = 0;
		$result['data']['msg'] ="无法连接,地址可能错误或者是密钥不正确".json_encode($info);
		return $result;
    }else{
         $result['status'] = 200;
    	 $result['data']['server_status'] = 1;
    	 $result['data']['msg'] = json_encode($info);
    	 return $result;
    }
    
    // return 
    
}

// 后台设置输出内容
function proxmoxlxc_ConfigOptions(){
    return [
        [
            'type'=>'text', 
            'name'=>'系统网卡名称', 
            'description'=>'选择局域网网卡',
            'placeholder'=>'vmbr0',
            'default'=>"vmbr0",
            'key'=>'net_name'
        ],
        [
            'type'=>'text', 
            'name'=>'处理器限制', 
            'description'=>'处理器性能百分比限制(0无限制)',
            'placeholder'=>'0-8192 （0无限制）',
            'default'=>"0",
            'key'=>'cpulimit'
        ],
        [
            'type'=>'text', 
            'name'=>'处理器权重', 
            'description'=>'越大此机器获取的CPU时间越长(0禁用)',
            'placeholder'=>'0-500000 （0禁用）',
            'default'=>"0",
            'key'=>'cpuunits'
        ],[
            'type'=>'text', 
            'name'=>'IP地址池', 
            'description'=>'IPV4地址范围(逗号隔开)',
            'placeholder'=>'172.16.0.2,172.16.0.20',
            'default'=>"172.16.0.2,172.16.0.20",
            'key'=>'ip_pool'
        ],
        [
            'type'=>'text', 
            'name'=>'掩码', 
            'description'=>'掩码地址',
            'placeholder'=>'24',
            'default'=>"24",
            'key'=>'Mask'
        ],[
            'type'=>'text', 
            'name'=>'IP网关', 
            'description'=>'网关',
            'placeholder'=>'172.16.0.1',
            'default'=>"172.16.0.1",
            'key'=>'gateway'
        ],[
            'type'=>'text', 
            'name'=>'DNS服务器', 
            'description'=>'域名解析服务器地址',
            'placeholder'=>'8.8.8.8',
            'default'=>"8.8.8.8",
            'key'=>'dns'
        ],[
            'type'=>'text', 
            'name'=>'系统盘存放盘', 
            'description'=>'存放系统盘的盘名',
            'placeholder'=>'local-lvm',
            'default'=>"local-lvm",
            'key'=>'system_disk'
        ],
        [
            'type'=>'dropdown', 
            'name'=>'交换内存', 
            'description'=>'酌情分配',
            'options'=>[
                     'noswap'=>'不分配',
                     '1024'=>'1024',
                     '1:1'=>'对等分配'
            ],
            'default'=>"noswap",
            'key'=>'swap'
        ],
        [
            'type'=>'dropdown', 
            'name'=>'类型', 
            'description'=>'NAT 或 标准',
            'options'=>[
                     'bz'=>'标准',
                     'nat'=>'nat',
            ],
            'default'=>"bz",
            'key'=>'nat'
        ],[
            'type'=>'text', 
            'name'=>'爱快地址', 
            'description'=>'请包含请求头加端口',
            'placeholder'=>'local-lvm',
            'default'=>"local-lvm",
            'key'=>'ikuai_url'
        ],[
            'type'=>'text', 
            'name'=>'爱快用户名', 
            'description'=>'类型为标准模式勿略',
            'placeholder'=>'admin',
            'default'=>"admin",
            'key'=>'ikuai_username'
        ],[
           'type'=>'text', 
            'name'=>'爱快密码', 
            'description'=>'类型为标准模式勿略',
            'placeholder'=>'password',
            'default'=>"password",
            'key'=>'ikuai_password'
        ],[
           'type'=>'text', 
            'name'=>'映射展示地址', 
            'description'=>'类型为标准模式勿略',
            'placeholder'=>'baidu.com',
            'default'=>"baidu.com",
            'key'=>'ikuai_ip'
        ],
        
        
    ];
		
}


// 前台页面
function proxmoxlxc_ClientArea($params){

    $info = [
        'info'=>['name'=>'信息'],
        'net'=>['name'=>'网络'],
        'disk'=>['name'=>'硬盘'],
        // 'firewalld'=>['name'=>'防火墙'],
        'snapshot'=>['name'=>'快照'],
        'connect'=>['name'=>'远程连接'],
        'rw'=>['name'=>'操作记录'],
        'demo'=>['name'=>'测试'],
        
    ];
    if($params['configoptions']['nat']=='nat'){
        $info['nat']=['name'=>'端口映射'];
    }

    return $info;
}


// 前台输出页面
function proxmoxlxc_ClientAreaOutput($params, $key){

    if($key == "demo"){
       
        // 信息页面
        return [
            'template'=>'templates/demo.html',
            'vars'=>[
                'params'=>$params
            ]
        ];

    }
    
    if($key == "info"){
       
        // 信息页面
        return [
            'template'=>'templates/info.html',
            'vars'=>[
                'params'=>$params
            ]
        ];

    }else if($key == "net"){
        // 网络页面
        return [
            'template'=>'templates/network.html',
            'vars'=>[
                'params'=>$params,
                'test'=>json_encode($params),
                'network'=>proxmoxlxc_GET_lxc_config($params)['data']['net0']
            ]
        ];

    }else if($key == "disk"){
        // 磁盘页面
        return [
            'template'=>'templates/disk.html',
            'vars'=>[
                'params'=>$params,
                'temp'=>json_encode(proxmoxlxc_GET_lxc_config($params)['data']),
                'disk'=>proxmoxlxc_GET_lxc_config($params)['data']['rootfs']
            ]
        ];

    }else if($key == "snapshot"){
        // 快照页面
        return [
            'template'=>'templates/snapshot.html',
            'vars'=>[
                'params'=>$params,
                'temp'=>json_encode(proxmoxlxc_GET_lxc_snapshot_list($params)),
                'snapshot'=>proxmoxlxc_GET_lxc_snapshot_list($params)
                
            ]
        ];

    }else if($key == "rw"){
        // 任务页面
        return [
            'template'=>'templates/rw.html',
            'vars'=>[
                'params'=>$params,
                'temp'=>json_encode(proxmoxlxc_tasks_get_list($params)),
                'tasks'=>proxmoxlxc_tasks_get_list($params)
                
            ]
        ];

    }else if($key == "nat"){
        // 映射页面
        // proxmoxlxc_nat_request($params,"","");
        // 获取映射列表
        
        $nat_list = proxmoxlxc_nat_get_list($params);
        
        if($nat_list['ErrMsg']=='Success'){
            //  return "登陆成功了 本币";
            // return $info;
            // echo json_encode($nat_list);
            
            return [
            'template'=>'templates/nat.html',
            'vars'=>[
                'params'=>$params,
                'list'=>$nat_list['Data'],
                'test'=>json_encode($params)
                // 'temp'=>json_encode(proxmoxlxc_tasks_get_list($params)),
                // 'tasks'=>proxmoxlxc_tasks_get_list($params)
                
            ]
        ];
            
            
         }else{
            //  return json_encode($info);
             return  [
            'template'=>'templates/error.html',
            'vars'=>[
                'error'=>[
                    'code'=>'502',
                    'msg'=>'无法连接映射服务器',
                    'info'=>$nat_list['ErrMsg']
                    ]
            ]
        ];
         }
        
        

    }else if($key == "connect"){
        // 任务页面
        // $ticket = proxmoxlxc_get_ticket($params);
            $port ="null";
        
        if($params['configoptions']['nat']=='nat'){
            $nat_list = proxmoxlxc_nat_get_list($params);
        
            if($nat_list['ErrMsg']=='Success'){
                
                foreach ($nat_list['Data']['data'] as $value){
                    if($value['lan_port']=='22'){
                        $port = $value['wan_port'];
                    }
                }
                
            }
            
        }else{
            $port = '22';
        }
        
        return [
            'template'=>'templates/connect.html',
            'vars'=>[
                'params'=>$params,
                'port'=>$port,
                'vnc'=>proxmoxlxc_vnc_if($params)

            ]
        ];

    }
    
}

// 允许执行的func
function proxmoxlxc_AllowFunction(){
	return [
		'client'=>["Getcurrent","delete_snapshot","RollBACK_snapshot","create_snapshot","nat_add","nat_del","Vnc"],
	];
}

/*图表*/


// 定义图表
function proxmoxlxc_Chart(){
 return [
    'cpu'=>[      // 图表type
        'title'=>'CPU使用率',   // 标题
        'select'=>[
				[
        					'name'=>'1小时',
        					'value'=>'hour'
        		],
        		[
        					'name'=>'一天',
        					'value'=>'day'
        					
        		],
        		[
        					'name'=>'七天',
        					'value'=>'week'
        		],[
        					'name'=>'一月',
        					'value'=>'month'
        		],
			]
        
        
    ],
    'mem'=>[      // 图表type
        'title'=>'内存使用率',   // 标题
        'select'=>[
				[
        					'name'=>'1小时',
        					'value'=>'hour'
        		],
        		[
        					'name'=>'一天',
        					'value'=>'day'
        					
        		],
        		[
        					'name'=>'七天',
        					'value'=>'week'
        		],[
        					'name'=>'一月',
        					'value'=>'month'
        		],
			]
        
    ],
    'disk'=>[      // 图表type
        'title'=>'硬盘IO',   // 标题
        'select'=>[
				[
        					'name'=>'1小时',
        					'value'=>'hour'
        		],
        		[
        					'name'=>'一天',
        					'value'=>'day'
        					
        		],
        		[
        					'name'=>'七天',
        					'value'=>'week'
        		],[
        					'name'=>'一月',
        					'value'=>'month'
        		],
			]
       
    ],
    'network'=>[      // 图表type
        'title'=>'网络流量',   // 标题
        'select'=>[
				[
        					'name'=>'1小时',
        					'value'=>'hour'
        		],
        		[
        					'name'=>'一天',
        					'value'=>'day'
        					
        		],
        		[
        					'name'=>'七天',
        					'value'=>'week'
        		],[
        					'name'=>'一月',
        					'value'=>'month'
        		],
			]
       
    ],
];
 
}

function proxmoxlxc_ChartData($params){
    
    // 计算一下机器运行时间 
    
    // 读取机器信息
    $uptime = proxmoxlxc_GET_lxc_info($params)['data']['uptime'];
    $timeframe = $params['chart']['select'];
    // if($uptime>=86400){
    //     // 一天了
    //     $timeframe = "day";
    // }else{
    //     // 一小时
    //     $timeframe = "hour";
    // }
    // 	return ['status'=>'error', 'msg'=>json_encode($uptime)];
    
    $info = json_decode(proxmoxlxc_request($params,"/api2/json/nodes/".$params['server_host']."/lxc/".$params['domain']."/rrddata?timeframe=".$timeframe),true);
    
    
    
    if($params['chart']['type'] == 'cpu'){
        
        // 组成数组
        
        foreach ($info['data'] as $value){
            
            $cpu = substr($value['cpu'] * 100,0,2);
            // $cpu = substr($value['cpu'],0,4);
            $result['data']['list'][0][] = [
                'time' =>date('Y-m-d H:i:s', $value['time']),
                'value' =>$cpu
                ];
                
        }
        
        $result["status"] = "success";  // 失败返回error

        $result["data"]["unit"] = "%";  //单位
        
        $result["data"]["chart_type"] = "line";  //支持 line线形,area区域填充
        
        $result["data"]["label"] = ["CPU使用率(%)"];  //对应每条线的label
        
        
    }elseif($params['chart']['type'] == 'mem'){
        
        // 组成数组
        
        foreach ($info['data'] as $value){
            
            if($value['maxmem']==""){
                break;
            }
            // $cpu = round(substr($value['cpu'],0,4) * 100,2);
            // $cpu = substr($value['cpu'],0,4);
            $max_mem = ($value['maxmem'] / 1024) / 1024 ;
            $run_mem = ($value['mem'] / 1024) / 1024 ;
            
            $mem = round($run_mem / $max_mem * 100,2);
            // (data.data.mem / 1024) / 1024 (run_mem/mem_max*100)
            
            // 内存百分比的
            $result['data']['list'][0][] = [
                'time' =>date('Y-m-d H:i:s', $value['time']),
                'value' =>$mem
                ];
            
                
        }
        
        $result["status"] = "success";  // 失败返回error

        $result["data"]["unit"] = "%";  //单位
        
        $result["data"]["chart_type"] = "line";  //支持 line线形,area区域填充
        
        $result["data"]["label"] = ["内存使用率(%)"];  //对应每条线的label
        
    }elseif($params['chart']['type'] == 'disk'){
        
        // 组成数组
        $dw = "K";
        foreach ($info['data'] as $value){
          
                $diskwrite = round($value['diskwrite'] / 1000,3);
                $diskread = round($value['diskread'] / 1000,3);
              

            // 计算IO
             $result['data']['list'][0][] = [
                'time' =>date('Y-m-d H:i:s', $value['time']),
                'value' =>$diskwrite
            ];
             $result['data']['list'][1][] = [
                'time' =>date('Y-m-d H:i:s', $value['time']),
                'value' =>$diskread
            ];
            
                
        }
        
        $result["status"] = "success";  // 失败返回error

        $result["data"]["unit"] = "K";  //单位
        
        $result["data"]["chart_type"] = "line";  //支持 line线形,area区域填充
        
        $result["data"]["label"] = ["写入速度(kb/s)","读取速度(kb/s)"];  //对应每条线的label
        
    }elseif($params['chart']['type'] == 'network'){
        
        // 组成数组
        foreach ($info['data'] as $value){
          
                $netin = round($value['netin'] / 1000,3);
                $netout = round($value['netout'] / 1000,3);
              

            // 计算IO
             $result['data']['list'][0][] = [
                'time' =>date('Y-m-d H:i:s', $value['time']),
                'value' =>$netin
            ];
             $result['data']['list'][1][] = [
                'time' =>date('Y-m-d H:i:s', $value['time']),
                'value' =>$netout
            ];
            
                
        }
        
        $result["status"] = "success";  // 失败返回error

        $result["data"]["unit"] = "K";  //单位
        
        $result["data"]["chart_type"] = "line";  //支持 line线形,area区域填充
        
        $result["data"]["label"] = ["入网流量(KB/s)","出网流量(KB/s)"];  //对应每条线的label
        
    }
    
    return $result;
   
}

// vnc
function proxmoxlxc_Vnc($params){
    
    // 判断是否存在VNC控制台的后端程序

    if(!proxmoxlxc_vnc_if($params)){

        return ['status'=>'error','msg'=>'VNC功能未启用'];

    }
    

    $ticket = proxmoxlxc_get_ticket($params);
    if($ticket){
        return ['status'=>'success','msg'=>'VNC连接创建成功','url'=>$params['server_http_prefix']."://".$params['server_ip'].":".$params['port']."/novnc/mgnovnc.html?xtermjs=1&console=lxc&node=".$params['server_host']."&vmid=".$params['domain']."&token=".$ticket];
    }
    
    
    // 不存在返回控制面板
    
    
    
    
    
    //  return ['status'=>'success','msg'=>'打开面板成功','url'=>$params['server_http_prefix']."://".$params['server_ip'].":".$params['port']];
}



// 开通方法
function proxmoxlxc_CreateAccount($params){
    
    
    
    
    
    
    $ip_while_num_max = 100; // IP生成循环次数，如果超过一定阈值直接弹出IP不足
    $ip_while_num = 0 ;
    $vmid = proxmoxlxc_nextid($params);
    /*构建IP地址 只能暂时分配/24的地址*/
   
    // 分割IP段
    
    // 先把段分割成起始跟结束
    $temp_ip_ = explode(",",$params['configoptions']['ip_pool']);
    
    $temp_ip_start = explode(".",$temp_ip_[0]);
    
    $temp_ip_end = explode(".",$temp_ip_[1]);
    
    // 开始分配IP地址
    while_ip:
        
    if($ip_while_num>=$ip_while_num_max ){
        return ['status'=>'error','msg'=>"无可用IP地址"];
    }
    
    $ip = $temp_ip_start[0].".".$temp_ip_start[1].".".$temp_ip_start[2].".".rand($temp_ip_start[3],$temp_ip_end[3]);
    
    
    // 验证IP是否占用
    $file = fopen(__DIR__."/ip_pool.json","r");
    
    $ip_json =json_decode(fread($file,filesize(__DIR__."/ip_pool.json")),true);
    
    fclose($file);
  
    
    foreach ($ip_json as $value){
        if($value['ip'] == $ip){
            // 存在相同的IP 冲突，重新获取IP
            $ip_while_num = $ip_while_num + 1;
            goto while_ip;
            
        }
    }
    
    // 临时写在这里
    // return ['status'=>'error','msg'=>proxmoxlxc_user_add($params,$ip,$params['password'],$vmid)];
    
    if(!proxmoxlxc_user_add($params,$ip,$params['password'],$vmid)){
        
        return ['status'=>'error','msg'=>"创建用户分配权限失败"];
       
    }
    
    
    // 构建JSON结构
    
    $json_network['server_vmid'] = $vmid;//对应主机VMID
    $json_network['bridge']=$params['configoptions']['net_name'];
    $json_network['ip'] = $ip;
    $json_network['mask'] = $params['configoptions']['Mask'];
    $json_network['gateway'] = $params['configoptions']['gateway'];
    $json_network['rate']=$params['configoptions_upgrade']['network'];
    
    $ip_json[$ip] = $json_network;
    
    // 写入库
    $file = fopen(__DIR__."/ip_pool.json","w");
    
    fwrite($file,json_encode($ip_json));
    
    fclose($file);
    
    // return ['status'=>'error','msg'=>json_encode($ip_json)];
    
    /*构建传递参数*/
    
    $network['name']='eth0';
    $network['bridge']=$params['configoptions']['net_name'];
    $network['gw']=$params['configoptions']['gateway'];
    $network['ip']=$ip."/".$params['configoptions']['Mask'];
    $network['rate']=$params['configoptions_upgrade']['network'];
    foreach ($network as $key=>$value){
        if($network_body==""){
            $network_body = $key."%3D".$value;
        }else{
            $network_body = $network_body."%2C".$key."%3D".$value;
        }
    }
    $data['start']=1;
    $data['ostemplate'] = $params['configoptions_upgrade']['os']; // 模板
    $data['vmid'] = $vmid; // vmid
    $data['hostname']=$params['domain']; 
    $data['unprivileged']=1;
    $data['password']=$params['password'];
    $data['rootfs']=$params['configoptions']['system_disk'].":".$params['configoptions_upgrade']['disk'];
    $data['cores']=$params['configoptions_upgrade']['cpu'];
    $data['memory']=$params['configoptions_upgrade']['memory'];
    $data['net0']=$network_body;
    $data['cmode']='console';
    $data['onboot'] = true;
    $data['nameserver'] = $params['configoptions']['dns'];
    $data['description'] = "来自: 魔方Proxmox-Lxc模块</br>开通用户:".$params['user_info']['username']."|".$params['user_info']['id']."</br>产品编号:".$params['hostid']."</br>产品密码:".$params['password'];//描述
    // $data['bwlimit']=$params['configoptions_upgrade']['network'];
    if($params['configoptions']['swap'] == '1:1'){
        // 对等分配swap
        $data['swap'] = $params['configoptions_upgrade']['memory'];
    }elseif($params['configoptions']['swap'] == '1024'){
        // 分配1G
        $data['swap'] = '1024';
    }
    
    /*请求后端创建服务器*/
    
    $info = json_decode(proxmoxlxc_request($params,"/api2/extjs/nodes/".$params['server_host']."/lxc",$data,"POST"),true);
    /*结果处理*/
    
    
    if($info['success']){
        // 判断是否为nat机器 如果是就执行下默认映射操作
        if($params['configoptions']['nat']=="nat"){
            $post['comment'] = $params['domain']."的远程端口";
            $post['type']='tcp+udp';
            $post['lan_port'] = "22";
            $post['lan_addr'] = $ip;
    
            
            
            $port_info = proxmoxlxc_nat_add($params,$post);
            
            if($port_info['ErrMsg']!="Success"){
                active_logs("创建端口映射时出现错误:".json_encode($port_info),$params['uid'],2);
                return ['status'=>'error','msg'=>json_encode($port_info)];
            }
            $update['port'] =$port_info['wan_port'];
        }
        
        
        
        /*更新数据列表*/
        
        
        
        $update['dedicatedip'] =$ip;
        $update['domain']=$data['vmid'];
        Db::name('host')->where('id', $params['hostid'])->update($update);
        
        
        
        
        /*写入本地文件*/
        
        return ['status'=>'success'];
    }else{
        return ['status'=>'error','msg'=>json_encode($info)];
    }
    
    
    
    
}

/*管理*/

// 删除机器
function proxmoxlxc_TerminateAccount ($params){
    
    
    if(!proxmoxlxc_user_del($params)){
        // return ['status'=>'error','msg'=>'用户删除失败'];
        active_logs($params['dedicatedip']."@pve用户删除失败",$params['uid'],2);
    }
    
    
    $info = json_decode(proxmoxlxc_request($params,"/api2/json/nodes/".$params['server_host']."/lxc/".$params['domain']."?purge=1&destroy-unreferenced-disks=1&force=1","","DELETE"),true);
    if($info['data']==null || $info == null){
        
        
        
        
        
        return ['status'=>'error','msg'=>"/api2/json/nodes/".$params['server_host']."/lxc/".$params['domain']."purge=1&destroy-unreferenced-disks=1&force=1"];
    }else{
        /*把操作保存到ip池中 释放IP*/
        
        $file = fopen(__DIR__."/ip_pool.json","r");
    
        $ip_json =json_decode(fread($file,filesize(__DIR__."/ip_pool.json")),true);
        
        fclose($file);
        
        
        
        // 删除IP
        unset($ip_json[$params['dedicatedip']]);
        
        // 写入库
        $file = fopen(__DIR__."/ip_pool.json","w");
        
        fwrite($file,json_encode($ip_json));
        
        fclose($file);
        
        // 判断是否为映射机
        
        // 判断是否为nat机器 如果是就执行下默认映射操作
        if($params['configoptions']['nat']=="nat"){
            
            // 操作删除映射
            $nat_list = proxmoxlxc_nat_get_list($params);
            
            if($nat_list['ErrMsg']=='Success'){
                // 获取成功
                
                foreach($nat_list['Data']['data'] as $key=>$value){
                    // 执行删除
                    $port['id'] = $value['id'];
                    $return_info = proxmoxlxc_nat_del($params,$port);
                    
                    // active_logs("执行删除映射结果:".json_encode($return_info),$params['uid'],2);
                    
                }
                
                
            }else{
                // 获取失败，写入日志
                active_logs("执行删除映射失败:".json_encode($nat_list),$params['uid'],2);
            }
            
            
        }
        
        
        // 便利映射数量
        
        // 删除映射
        
        
        return ['status'=>'success'];
    }
    

}

/*电源管理*/

// 开机
function proxmoxlxc_On($params){
    $info = json_decode(proxmoxlxc_request($params,"/api2/json/nodes/".$params['server_host']."/lxc/".$params['domain']."/status/start",$data,"POST"),true);
    if($info['data']==null || $info == null){
        return ['status'=>'error','msg'=>'操作失败'];
    }else{
        return ['status'=>'success'];
    }
    
    
    
}
// 关机
function proxmoxlxc_Off($params){
    $info = json_decode(proxmoxlxc_request($params,"/api2/json/nodes/".$params['server_host']."/lxc/".$params['domain']."/status/shutdown",$data,"POST"),true);
    if($info['data']==null || $info == null){
        return ['status'=>'error','msg'=>'操作失败'];
    }else{
        return ['status'=>'success'];
    }
    
    
}

// 重启
function proxmoxlxc_Reboot($params){
    $info = json_decode(proxmoxlxc_request($params,"/api2/json/nodes/".$params['server_host']."/lxc/".$params['domain']."/status/reboot",$data,"POST"),true);
    if($info['data']==null || $info == null){
        return ['status'=>'error','msg'=>'操作失败'];
    }else{
        return ['status'=>'success'];
    }
}

// 强制关机
function proxmoxlxc_HardOff ($params){
     $info = json_decode(proxmoxlxc_request($params,"/api2/json/nodes/".$params['server_host']."/lxc/".$params['domain']."/status/stop",$data,"POST"),true);
    if($info['data']==null || $info == null){
        return ['status'=>'error','msg'=>'操作失败'];
    }else{
        return ['status'=>'success'];
    }
}


// 暂停
function proxmoxlxc_SuspendAccount ($params){
    
}
// 解除暂停
function proxmoxlxc_UnsuspendAccount($params){
    
}


/*功能函数*/

function proxmoxlxc_Getcurrent($params){
    $post = input('post.');

    
    // 验证PVE状态
    if(!proxmoxlxc_Pvestatus($params)){
        return ['status'=>'error','msg'=>'受控端异常'];
    }
    
    
    return proxmoxlxc_GET_lxc_info($params);
}

// 获取VMID
function proxmoxlxc_nextid($params){

        if(!proxmoxlxc_Pvestatus($params)){
            // return ['status'=>'error','msg'=>'受控端异常'];
            return 0;
        }else{
             $request =  json_decode(proxmoxlxc_request($params,"/api2/json/cluster/nextid"),true);
             return $request['data'];
        }
    }

// 检测pve状态
function proxmoxlxc_Pvestatus($params){
    
    return 1;
    
}

function proxmoxlxc_status($params){
    

    $request =  json_decode(proxmoxlxc_request($params,"/api2/json/nodes/".$params['server_host']."/lxc/".$params['domain']."/status/current"),true);
    $result['status'] = 'success';
    if($request['data']['status']=='running'){
        // 运行中
        $result['data']['status'] = 'on';
		$result['data']['des'] = '运行中';
    }elseif($request['data']['status']=='stopped'){
        $result['data']['status'] = 'off';
		$result['data']['des'] = '关机';
    }else{
        $result['data']['status'] = 'unknown';
        $result['data']['des'] = '未知';
    }
    
    return $result;
    
}

// 获取LXC信息
function proxmoxlxc_GET_lxc_info($params){
    $request =  json_decode(proxmoxlxc_request($params,"/api2/json/nodes/".$params['server_host']."/lxc/".$params['domain']."/status/current"),true);
    return $request;
    
}

// 获取LXC配置信息
function proxmoxlxc_GET_lxc_config($params){
    $request =  json_decode(proxmoxlxc_request($params,"/api2/json/nodes/".$params['server_host']."/lxc/".$params['domain']."/config"),true);
    
    if($request['data']==null||$request==null){
        return false;
    }
    
    // 把网卡配置格式化一下
     $temp_net_info = explode(",",$request['data']['net0']);
    foreach ($temp_net_info as $value){
        
        $temp = explode("=",$value);
        
        $temp_ini[$temp[0]]=$temp[1]; 
         
         
    }
    $request['data']['net0'] = $temp_ini;
    
    // 格式化硬盘
    $temp_disk_info = explode(",",$request['data']['rootfs']);
   
    $request['data']['rootfs'] =  explode(",",$request['data']['rootfs']);;
    
    
    return $request;
    
}

/*快照相关*/

// 获取快照列表
function proxmoxlxc_GET_lxc_snapshot_list($params){
    $request =  json_decode(proxmoxlxc_request($params,"/api2/json/nodes/".$params['server_host']."/lxc/".$params['domain']."/snapshot/"),true);
    $temp = [];
    foreach ($request['data'] as $value){
        
        if($value['name']=="current"){
             continue;
        }
        $value['snaptime'] = date('Y-m-d H:i:s',$value['snaptime']);
        
        $value['description'] = mb_convert_encoding($value['description'], 'GBK','UTF-8');
        // $a = 1;
        array_push($temp,$value);
        
        // $temp['name'] = $value['name'];
    }
    
    return $temp;
    
}

// 删除快照
function proxmoxlxc_delete_snapshot($params){
    
    $post = input('post.');
    
    $request =  json_decode(proxmoxlxc_request($params,"/api2/json/nodes/".$params['server_host']."/lxc/".$params['domain']."/snapshot/".$post['name'],"","DELETE"),true);
    
    if($request['data']==null||$request==null){
        return false;
    }
    
    return ['status'=>'200','msg'=>'快照删除成功'];
    
}

// 回滚
function proxmoxlxc_RollBACK_snapshot($params){
    
    $post = input('post.');
    
    $request =  json_decode(proxmoxlxc_request($params,"/api2/json/nodes/".$params['server_host']."/lxc/".$params['domain']."/snapshot/".$post['name']."/rollback","","POST"),true);
    
    if($request['data']==null||$request==null){
        return false;
    }
    
    return ['status'=>'200','msg'=>'回滚成功'];
    
}

// 创建快照
function proxmoxlxc_create_snapshot($params){
    
    $post = input('post.');
    
    $data['snapname'] = $post['name'];
    $data['description'] = $post['description'];
    
    $request =  json_decode(proxmoxlxc_request($params,"/api2/json/nodes/".$params['server_host']."/lxc/".$params['domain']."/snapshot/",$data,"POST"),true);
    
    if($request['data']==null||$request==null){
        return false;
    }
    
    return ['status'=>'200','msg'=>'创建成功'];
    
}

/*任务相关*/

// 获取任务列表
function proxmoxlxc_tasks_get_list($params){
    
    
    $request =  json_decode(proxmoxlxc_request($params,"/api2/json/nodes/".$params['server_host']."/tasks?vmid=".$params['domain']."&start=0&limit=10"),true);
    
    if($request['data']==null||$request==null){
        return false;
    }
    
    
    $temp = [];
    foreach ($request['data'] as $value){
        
        $value['starttime'] = date('Y-m-d H:i:s',$value['starttime']);
        $value['endtime'] = date('Y-m-d H:i:s',$value['endtime']);
        
        // $a = 1;
        array_push($temp,$value);
        
        // $temp['name'] = $value['name'];
    }
    
    return $temp;
    
    
    
    // return $request['data'];
    
    
}


/*
端口映射相关操作
*/



// 获取列表
function proxmoxlxc_nat_get_list($params){
    $url = $params['configoptions']['ikuai_url']."/Action/call";
    


    $data['func_name'] = "dnat";
    $data['action'] = "show";
    $data['param'] = [
     "TYPE"=>"total,data",
     "limit"=>"0,20",
     "FINDS"=>"lan_addr,lan_port,wan_port,comment",
     "KEYWORDS"=> $params['dedicatedip']
     
     ];
     
     $info = json_decode(proxmoxlxc_nat_request($params,$url,$data),true);
     
    //  echo json_encode($data);
     
     return $info;
     
    //  return proxmoxlxc_nat_request($params,$url,$dataa);
}

// 添加映射
function proxmoxlxc_nat_add($params,$post=""){
    
    // 判断是从哪里请求的
    if($post==""){
        // 从前台请求的 需要手动拉一下内网ip
        $post = input('post.');
        $post['lan_addr'] = $params['dedicatedip'];
    }
    
    // 如果没传递外网端口，就随机生成
    if($post['wan_port']==""){
         $post['wan_port'] = rand(20000,60000);
    }
    
    $url = $params['configoptions']['ikuai_url']."/Action/call";
    

    
    $data['func_name'] = "dnat";
    $data['action'] = "add";
    $data['param'] = [
     "enabled"=>"yes",
     "comment"=>$post['comment'],
     "interface"=>"all",
     "lan_addr"=> $post['lan_addr'],
     "protocol"=>$post['type'],
     "wan_port"=>$post['wan_port'],
     "lan_port"=>$post['lan_port'],
     "src_addr"=>""
     
     ];
    
     $info = json_decode(proxmoxlxc_nat_request($params,$url,$data),true);
     $info['wan_port'] = $post['wan_port'];
    //  echo json_encode($data);
    
    // 写入后台日志
     active_logs("执行创建映射函数返回结果:".json_encode($info),$params['uid'],2);
     return $info;
    
}
// 删除映射
function proxmoxlxc_nat_del($params,$post=""){
    
    
    // 判断是从哪里请求的
    if($post==""){
        // 从前台请求的
        $post = input('post.');
    }
    
  
    
    $url = $params['configoptions']['ikuai_url']."/Action/call";
    

    
    $data['func_name'] = "dnat";
    $data['action'] = "del";
    $data['param'] = [
     "id"=>$post['id'],
    
     
     ];
    
     $info = json_decode(proxmoxlxc_nat_request($params,$url,$data),true);

    //  echo json_encode($data);
    
    // 写入后台日志
     active_logs("执行删除映射函数返回结果:".json_encode($info),$params['uid'],2);
     return $info;
    
}

// 请求方法
function proxmoxlxc_nat_request($params,$url,$data){
    
    /*登陆获取cookie*/
    // 定义cookie存放路径
    $cookie_file = dirname(__FILE__).'/cookie/ikuai_'.$params['server_ip'].'.cookie';
    $login_url = $params['configoptions']['ikuai_url']."/Action/login";
    // $post_string =  "{\"username\":\"admin\",\"passwd\":\"4e62f5790eea7611dd3c3e0ebc6d9c18\",\"pass\":\"c2FsdF8xMXhpYW93ZWlDMTIz\",\"remember_password\":\"true\"}";
    
    $post_data['username'] = $params['configoptions']['ikuai_username'];
    
    $post_data['passwd']= md5($params['configoptions']['ikuai_password']);
    
    $post_data['pass'] = md5("salt_11".$params['configoptions']['ikuai_password']);
    
    $post_data['remember_password'] = true;
    
    
    
    
    $ch = curl_init($login_url); //初始化
	curl_setopt($ch, CURLOPT_HEADER, 0); //不返回header部分
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //返回字符串，??直接输出
	curl_setopt($ch, CURLOPT_COOKIEJAR,  $cookie_file); //存储cookies
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
	 curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE); // 屏蔽SSL
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE); // 屏蔽SSL
	curl_exec($ch);
	curl_close($ch);
// 	echo json_encode($post_data);
    
    
    // 使用cookie请求
    
    $cookie_file = dirname(__FILE__).'/cookie/ikuai_'.$params['server_ip'].'.cookie';
    
    // $url =$params['configoptions']['ikuai_url']."/Action/call";
    // $post_string = "{\"func_name\":\"dnat\",\"action\":\"show\",\"param\":{\"TYPE\":\"total,data\",\"limit\":\"0,20\",\"ORDER_BY\":\"\",\"ORDER\":\"\",\"FINDS\":\"lan_addr,lan_port,wan_port,comment\",\"KEYWORDS\":\"172.16.0.2\",\"FILTER1\":\"\",\"FILTER2\":\"\",\"FILTER3\":\"\",\"FILTER4\":\"\",\"FILTER5\":\"\"}}";
    
     
        //  echo "get:".json_encode($dataa);
         
        $ch = curl_init($url);
    	curl_setopt($ch, CURLOPT_HEADER, 0);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    	curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file); //使?上?获取的cookies
    	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    	 curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE); // 屏蔽SSL
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE); // 屏蔽SSL
    	$response = curl_exec($ch);
    	curl_close($ch);
    	return $response;
    
    
    
}


/*用户管理相关方法*/

// 创建用户
function proxmoxlxc_user_add($params,$username,$password,$vmid){
    // return FALSE;
    
    // 构建请求参数
    $data['userid'] = $username."@pve";
    $data['password'] = $password;
    $data['comment'] = "用户:".$params['user_info']['username']."|".$params['user_info']['id']."的VNC账号,服务器编号:".$vmid;
    
    $request =  json_decode(proxmoxlxc_request($params,"/api2/extjs/access/users",$data,"POST"),true);
    
    if($request==null){
        return FALSE;
    }
    
    if($request['success']){
        // 注册成功 开始分配权限
        
        // 构建参数
        $qx['path'] = "%2Fvms%2F".$vmid;
        $qx['users']= $data['userid'];
        $qx['roles'] = "PVEVMUser";
        
        // 请求
        $request1 =  json_decode(proxmoxlxc_request($params,"/api2/extjs/access/acl",$qx,"PUT"),true);
        
        return $request1;
        
    }
    
    // return "游结果咩:".json_encode($request);
}

// 删除用户
function proxmoxlxc_user_del($params){
    
    $request =  json_decode(proxmoxlxc_request($params,"/api2/extjs/access/users/".$params['dedicatedip']."@pve","","DELETE"),true);
    
    if($request==null){
        return FALSE;
    }
    
    if($request['success']){
        // 删除成功 开始分配权限
        return true;
        
        
    }
    
    
}

// 禁用用户
function proxmoxlxc_user_ban(){
    
    
}

// 解除禁用用户
function proxmoxlxc_user_unban(){
    
    
}

// 获取用户访问VNC授权
function proxmoxlxc_get_ticket($params){
    
    $curl = curl_init();

    $url = $params['server_http_prefix']."://".$params['server_ip'].":".$params['port']."/api2/extjs/access/ticket";
    curl_setopt($curl,CURLOPT_URL,$url); 
    curl_setopt($curl,CURLOPT_POSTFIELDS,"username=".$params['dedicatedip']."&password=".$params['password']."&realm=pve&new-format=1"); 
    curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,FALSE); // 屏蔽SSL
    curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,FALSE); // 屏蔽SSL
    curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);
    
    $response = curl_exec($curl);
    $err = curl_error($curl);
    
    curl_close($curl);
    
    if ($err) {
        // 判断服务器请求正常
      return FALSE;
    } else {
      
        $response = json_decode($response,true);
        if($response['success']){
            return $response['data']['ticket'];
        }else{
            return FALSE;
        }
      
    }

    
}

// VNC判断是否存在后端文件
function proxmoxlxc_vnc_if($params){
    $curl = curl_init();
    $url = $params['server_http_prefix']."://".$params['server_ip'].":".$params['port']."/novnc/mgnovnc.html";
    curl_setopt($curl,CURLOPT_URL,$url); 
    curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,FALSE); // 屏蔽SSL
    curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,FALSE); // 屏蔽SSL
    curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);
    $response = strval(curl_exec($curl));
    $err = curl_error($curl);
    
    curl_close($curl);
    

    if ($err) {
      return FALSE;
    } else {
    //   echo $response;
        if(stripos($response,"Loading")){
            return true;
        }else{
            return FALSE;
        }
        // echo "是字符串";
    
    }
        
    
}

function proxmoxlxc_request($params,$url,$data="",$method='GET'){
        
        $url = $params['server_http_prefix']."://".$params['server_ip'].":".$params['port'].$url;
        if($data!=""){
            
            if($data['no']){
                // 不格式化
                
                /*注意一下！！！如果后续其他功能BOOM了 绝对是这个造成的！！！！ */
                unset($data['no']);
                
                $body = json_encode($data);
            }else{
                // 格式化一下要传递的格式
            foreach ($data as $key=>$val) {
                if($body == ""){
                    // 首次
                    $body = $key."=".$val."&";
                }else{
                    $body = $body.$key."=".$val."&";
                }
            }
                
            }
            
            
        }
        // return $url;
        $ch = curl_init(); //初始化CURL句柄
        curl_setopt($ch, CURLOPT_URL, $url); //设置请求的URL
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); //设为TRUE把curl_exec()结果转化为字串，而不是直接输出
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method); //设置请求方式
        curl_setopt($ch,CURLOPT_HTTPHEADER,array("X-HTTP-Method-Override: $method","Authorization:PVEAPIToken=".$params['accesshash']));//设置HTTP头信息 携带TOKEN
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);//设置提交的字符串
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE); // 屏蔽SSL
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE); // 屏蔽SSL
        // curl_setopt($ch, CURLOPT_TIMEOUT,$this->Curl_TimeOut);
        $document = curl_exec($ch);//执行预定义的CURL
        $err = curl_error($ch);
        curl_close($ch);
        $r = mb_convert_encoding($document, 'UTF-8','GBK');
        
        // 错误处理
        
        if ($err) {
          return "errorCurl:".$err;
        } else {
          return $r;
        }
        
        
       
    }

?>
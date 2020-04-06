<?php
////////////////////////////////////////////////////////////////////////////////
//
function project_stat(){
    return "SELECT if(isnull(parp.par_name),prj.project_name, concat('+-',prj.project_name)) fullname,
        tech.name techname,tech.wallpayback wallpayback,pl.callnum,pl.b2bcallnum,s.name statname,prj.*,
        ifnull(ltc.lname,'') ltcname
	FROM ps_project prj 
                left join (select project_id par_id,project_name par_name from ps_project) parp on prj.parent=parp.par_id
                left join ps_teh_podkl tech on prj.technology=tech.id
                left join ps_status s on prj.status=s.id
                left join ltc on prj.ltc=ltc.lid
                left join (select prl.project_id,sum(pls.cs-1) b2bcallnum,count(prl.list_id) callnum 
                    from ps_project_list prl left join ps_list pls on pls.list_id=prl.list_id
                    group by prl.project_id) pl using(project_id)
	order by if(isnull(parp.par_name),prj.project_name, concat(parp.par_name,' ',prj.project_name))";
}
//
////////////////////////////////////////////////////////////////////////////////
// получаем итоговые цифры проекта с учетом подпроектов
function sub_project_stat($project_id){
    return "select count(prl.list_id) callnum,sum(pls.cs-1) b2bcallnum 
                        from ps_project_list prl
                        left join ps_list pls on pls.list_id=prl.list_id
                where prl.project_id in (select project_id from ps_project where parent=".
                                    $project_id ." and project_id!=parent)";
}
//
////////////////////////////////////////////////////////////////////////////////
// статистика статусов заявок проекта               
function call_status_stat($project_id){
    return "select project_id,pls.cs,count(pl.list_id) callnum,ld.status,s.name statname
                from ps_project_list pl 
                    left join ps_list_dop ld using(list_id)  
                    inner join ps_list pls on pls.list_id=ld.list_id
                    left join ps_status s on ld.status=s.id  
                where project_id=".$project_id ." group by project_id,pls.cs,ld.status,s.name 
                order by project_id,ld.status ";
}
// 
////////////////////////////////////////////////////////////////////////////////
?>
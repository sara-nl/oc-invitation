select distinct i_id, user_cloud_id, user_name, remote_user_cloud_id, remote_user_name
 from (
  select i.id as i_id, i.sender_cloud_id as user_cloud_id, i.sender_name as user_name, i.recipient_cloud_id as remote_user_cloud_id, i.recipient_name as remote_user_name from oc_mesh_invitations i
   where i.provider_domain = 'rd-1.nl'
  union all
  select ii.id as i_id, ii.recipient_cloud_id as user_cloud_id, ii.recipient_name as user_name, ii.sender_cloud_id as remote_user_cloud_id, ii.sender_name as remote_user_name from oc_mesh_invitations ii
   where ii.recipient_domain = 'rd-1.nl'
 ) s
group by i_id;
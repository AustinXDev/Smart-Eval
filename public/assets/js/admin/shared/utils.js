
export function nameToInitials(name){
  if(!name) return '';

  return name.split(' ').filter(part => part.length > 0).map(part => part[0].toUpperCase()).join('');
}

export function formatStatus(is_active){
  if(is_active === 1){
    return { text : 'Active', color: 'bg-green-600'};
  } else {
    return { text : 'Inactive', color: 'bg-red-600'};
  }
}
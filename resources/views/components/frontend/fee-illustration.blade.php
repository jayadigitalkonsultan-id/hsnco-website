@props(['type'])
<svg viewBox="0 0 120 100" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
<path d="M10 86H110" opacity=".25"/>
@switch($type)
@case('clock')<circle cx="60" cy="46" r="30"/><circle cx="60" cy="46" r="24" opacity=".3"/><path d="M60 26V46L75 55M60 16V20M90 46H86M60 76V72M30 46H34"/><circle cx="60" cy="46" r="2"/>@break
@case('document')<path d="M36 14H71L86 29V78H36ZM71 14V29H86M47 40H74M47 49H74M47 58H61M63 68L68 73L79 62"/>@break
@case('target')<circle cx="57" cy="47" r="30"/><circle cx="57" cy="47" r="20" opacity=".5"/><circle cx="57" cy="47" r="9"/><path d="M57 47L88 16M88 16V29M88 16H75"/>@break
@case('calendar')<rect x="28" y="22" width="64" height="56" rx="2"/><path d="M28 37H92M44 15V29M76 15V29M40 49H47M56 49H63M72 49H79M40 62H47M56 62H63"/><circle cx="77" cy="64" r="7"/>@break
@case('scales')<path d="M60 17V77M44 78H76M27 32H93M36 32L23 57H49ZM84 32L71 57H97ZM23 57Q36 73 49 57M71 57Q84 73 97 57"/><circle cx="60" cy="26" r="4"/>@break
@case('project')<rect x="42" y="13" width="36" height="23"/><rect x="16" y="58" width="36" height="23"/><rect x="68" y="58" width="36" height="23"/><path d="M60 36V46H34V58M60 46H86V58M51 24H69M25 69H43M77 69H95"/>@break
@endswitch
</svg>
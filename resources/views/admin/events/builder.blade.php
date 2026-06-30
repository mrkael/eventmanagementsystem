<x-layouts.admin title="Event Page Builder" heading="Event Page Builder" subheading="{{ $event->title }}">
    <form method="POST" action="{{ route('admin.events.builder.update',$event) }}" class="grid gap-6 xl:grid-cols-[1fr_360px]">
        @csrf @method('PUT')
        <input type="hidden" name="page_sections" id="page_sections" value="{{ e(json_encode($sections)) }}">
        <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm"><div class="flex flex-wrap items-center justify-between gap-3"><h2 class="font-semibold">Sections</h2><div class="flex gap-2"><select id="section-type" class="min-h-11 rounded-lg border border-slate-300 px-3 text-sm">@foreach($sectionTypes as $type)<option value="{{ $type }}">{{ ucfirst(str_replace('_',' ',$type)) }}</option>@endforeach</select><button type="button" id="add-section" class="btn btn-primary btn-md">Add Section</button></div></div><div id="builder-list" class="mt-4 space-y-3"></div><div class="mt-5 flex flex-wrap gap-3"><button class="btn btn-primary btn-md">Save draft</button><a href="{{ route('admin.events.preview',$event) }}" class="btn btn-outline-primary btn-md">Preview Event Page</a></div></section>
        <aside class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm"><h2 class="font-semibold">Publish</h2><p class="mt-2 text-sm text-slate-600">Publishing makes the latest saved page version available on the public event URL.</p></aside>
    </form>
    <form method="POST" action="{{ route('admin.events.builder.publish',$event) }}" class="mt-4">@csrf<button class="btn btn-info btn-md">Publish Event Page</button></form>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const hidden = document.getElementById('page_sections');
        const list = document.getElementById('builder-list');
        const typeSelect = document.getElementById('section-type');
        let sections = JSON.parse(hidden.value || '[]');
        const sync = () => hidden.value = JSON.stringify(sections.map((s,i)=>({...s,sort_order:i})));
        const render = () => { list.innerHTML = ''; sections.forEach((s,i)=>{ const row=document.createElement('div'); row.className='rounded-lg border border-slate-200 p-4'; row.innerHTML=`<div class="flex flex-wrap items-center gap-2"><span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold">${s.type.replace('_',' ')}</span><button type="button" data-up="${i}" class="rounded border px-2 py-1 text-xs">Up</button><button type="button" data-down="${i}" class="rounded border px-2 py-1 text-xs">Down</button><button type="button" data-dup="${i}" class="rounded border px-2 py-1 text-xs">Duplicate</button><button type="button" data-del="${i}" class="rounded border border-red-200 px-2 py-1 text-xs text-red-700">Remove</button></div><label class="mt-3 block text-sm font-medium">Title</label><input data-title="${i}" value="${(s.title||'').replaceAll('"','&quot;')}" class="mt-1 min-h-11 w-full rounded-lg border border-slate-300 px-3 text-sm"><label class="mt-3 block text-sm font-medium">Content</label><textarea data-content="${i}" rows="4" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">${s.content||''}</textarea>`; list.appendChild(row); }); sync(); };
        document.getElementById('add-section').addEventListener('click',()=>{sections.push({id:crypto.randomUUID(),type:typeSelect.value,title:typeSelect.options[typeSelect.selectedIndex].text,content:'',settings:{}});render();});
        list.addEventListener('input',e=>{ if(e.target.dataset.title) sections[e.target.dataset.title].title=e.target.value; if(e.target.dataset.content) sections[e.target.dataset.content].content=e.target.value; sync();});
        list.addEventListener('click',e=>{ const i=e.target.dataset.up ?? e.target.dataset.down ?? e.target.dataset.dup ?? e.target.dataset.del; if(i===undefined)return; const n=Number(i); if(e.target.dataset.up&&n>0)[sections[n-1],sections[n]]=[sections[n],sections[n-1]]; if(e.target.dataset.down&&n<sections.length-1)[sections[n+1],sections[n]]=[sections[n],sections[n+1]]; if(e.target.dataset.dup)sections.splice(n+1,0,{...sections[n],id:crypto.randomUUID(),title:sections[n].title+' Copy'}); if(e.target.dataset.del)sections.splice(n,1); render();});
        render();
    });
    </script>
</x-layouts.admin>

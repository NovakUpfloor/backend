<?php 
$bg   = DB::table('heading')->where('halaman','Berita')->orderBy('id_heading','DESC')->first();
 ?>
<!--Inner Header Start-->
<section class="wf100 p80 inner-header" style="background-image: url('{{ asset('assets/upload/image/'.$bg->gambar) }}'); background-position: bottom center;">
   <div class="container">
      <h1>{{ $title }}</h1>
   </div>
</section>
<!--Inner Header End--> 
<!--Blog Start-->
<section class="wf100 p80 blog">
   <div class="blog-grid">
      <div class="container">
         <div class="row">
            <?php foreach($berita as $notberita) { ?>
            <!--Blog Small Post Start-->
            <div class="col-md-6 col-sm-12">
               <div class="blog-post">
                  <div class="blog-thumb"> <a href="{{ asset('berita/read/'.$notberita->slug_berita) }}"><i class="fas fa-link"></i></a> <img src="{{ asset('assets/upload/image/thumbs/'.$notberita->gambar) }}" alt="><?php  echo $notberita->judul_berita ?>"> </div>
                  <div class="post-txt">
                     <h5><a href="{{ asset('berita/read/'.$notberita->slug_berita) }}"><?php  echo $notberita->judul_berita ?></a></h5>
                     <ul class="post-meta">
                        <li> <a href="{{ asset('berita/read/'.$notberita->slug_berita) }}"><i class="fas fa-calendar-alt"></i> {{ tanggal('tanggal_id',$notberita->tanggal_post)}}</a> </li>
                        <li> <a href="{{ asset('berita/read/'.$notberita->slug_berita) }}"><i class="fas fa-comments"></i> {{ $notberita->nama_kategori }}</a> </li>
                     </ul>
                     <p><?php echo \Illuminate\Support\Str::limit(strip_tags($notberita->isi), 100, $end='...') ?></p>
                     <a href="{{ asset('berita/read/'.$notberita->slug_berita) }}" class="read-post">Baca detail</a>
                  </div>
               </div>
            </div>
            <!--Blog Small Post End--> 
            <?php } ?>
         </div>
	
         <div class="gt-pagination">
            {{ $berita->links() }}
         </div>

      </div>
   </div>
</section>
<!--Blog End--> 
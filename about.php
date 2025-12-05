<?php
/**
 * about.php - Trang về chúng tôi
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Về Chúng Tôi';
include __DIR__ . '/includes/header.php';
?>

<!-- Hero Section -->
<section style="padding: 0; position: relative;">
    <div style="height: 400px; background: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), 
                url('https://lh3.googleusercontent.com/aida-public/AB6AXuBCnh0YUgdReFiNGcYTMh8EVr1lG143P_jamH4N0-TI1suQw0bV8qKVjmDlJIhVkuSxrksar5TcWft89Pye7oaT_HB0g3jW-bttqwwp0kEEiftgqFl8b_1iihOKSmRlwiIjN2w65R4UWFTCZd9sYktTWr02RTjWPXWq9GtCJPi2o0Yd0E3bdcFgrNp8_FCxqdxwNURowCDOFoY1lctqZXQ52MDsgGGgcqr_WTkl2XlIIRj2M7XGtaIV0htCJXPEB5rDYVoDxG4CmbQQ') center/cover no-repeat;
                display: flex; align-items: center; justify-content: center; text-align: center;">
        <div style="max-width: 800px; padding: 0 1rem;">
            <h1 style="font-size: 3.5rem; font-weight: 900; color: white; margin-bottom: 1rem;">
                Về Xanh Organic
            </h1>
            <p style="font-size: 1.25rem; color: white; opacity: 0.95;">
                Kết nối bạn với nguồn thực phẩm sạch, tươi ngon từ nông trại địa phương
            </p>
        </div>
    </div>
</section>

<!-- Story Section -->
<section style="padding: 4rem 1rem;">
    <div style="max-width: 1280px; margin: 0 auto;">
        <div style="display: grid; grid-template-columns: 1fr; gap: 3rem; align-items: center;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                <!-- Left Column - Image -->
                <div>
                    <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuCEyEJTYxEoNf4zSGB03Sh95hZUHawcHYvrbZkcJ9579Xm_0f-xLP3wc1pQYVWicpL4pNeVC6tg4gz3vRvhzrMCme9vRzfizcBBI4846cJOhg2pKOwQJjDiN9PYvQxRKE84jL6b7tDTfUPlSOJUpQDEMu5gaXaAQtuv0Ee2XHW2OWS23CbH3PDzy_0IGaHi7j408dZAGzsU-jIFvZYk0sfL_SfrJBxh1OKQLrQ1Ietsw2XWhHTg3yv0dMGALImebSfRqlFUzg6EM-9B" 
                         alt="Farmers" style="width: 100%; border-radius: 1rem; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                </div>
                
                <!-- Right Column - Content -->
                <div>
                    <h2 style="font-size: 2rem; font-weight: 700; margin-bottom: 1.5rem; color: var(--text-light);">
                        Câu Chuyện Của Chúng Tôi
                    </h2>
                    <p style="color: var(--text-light); line-height: 1.8; margin-bottom: 1rem;">
                        Xanh Organic được sinh ra từ một mong muốn giản dị: kết nối lại với đất mẹ và mang đến cho cộng đồng những thực phẩm tinh khiết, lành mạnh và được trồng bền vững.
                    </p>
                    <p style="color: var(--text-light); line-height: 1.8; margin-bottom: 1rem;">
                        Mọi thứ bắt đầu từ một mảnh vườn nhỏ của gia đình và một giấc mơ lớn. Những người sáng lập của chúng tôi, được truyền cảm hứng từ trí tuệ nông nghiệp truyền thống Việt Nam và niềm đam mê với lối sống lành mạnh, đã hình dung về một nơi mà mọi người có thể tin tưởng vào thực phẩm trên bàn ăn của mình.
                    </p>
                    <p style="color: var(--text-light); line-height: 1.8;">
                        Hôm nay, giấc mơ đó đã đơm hoa kết trái thành một cộng đồng thịnh vượng gồm những người nông dân địa phương và những người tiêu dùng có ý thức. Chúng tôi hợp tác chặt chẽ với các nhà vườn tận tâm trên khắp Việt Nam.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Core Values -->
<section style="padding: 4rem 1rem; background: rgba(182, 230, 51, 0.05);">
    <div style="max-width: 1280px; margin: 0 auto;">
        <div style="text-align: center; margin-bottom: 3rem;">
            <h2 style="font-size: 2.5rem; font-weight: 700; margin-bottom: 1rem;">Giá Trị Cốt Lõi</h2>
            <p style="font-size: 1.125rem; color: var(--muted-light);">
                Những nguyên tắc định hướng cho từng hạt giống chúng tôi gieo và từng mớ rau chúng tôi trao
            </p>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem;">
            <div style="background: white; padding: 2rem; border-radius: 1rem; text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.05); transition: transform 0.3s;">
                <div style="width: 80px; height: 80px; margin: 0 auto 1.5rem; border-radius: 50%; background: rgba(182, 230, 51, 0.2); display: flex; align-items: center; justify-content: center;">
                    <span class="material-symbols-outlined" style="font-size: 2.5rem; color: var(--primary-dark);">compost</span>
                </div>
                <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1rem;">Bền Vững</h3>
                <p style="color: var(--muted-light); line-height: 1.6;">
                    Chúng tôi thực hành và thúc đẩy các phương pháp canh tác giúp làm giàu đất, bảo vệ đa dạng sinh học.
                </p>
            </div>
            
            <div style="background: white; padding: 2rem; border-radius: 1rem; text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.05); transition: transform 0.3s;">
                <div style="width: 80px; height: 80px; margin: 0 auto 1.5rem; border-radius: 50%; background: rgba(182, 230, 51, 0.2); display: flex; align-items: center; justify-content: center;">
                    <span class="material-symbols-outlined" style="font-size: 2.5rem; color: var(--primary-dark);">groups</span>
                </div>
                <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1rem;">Cộng Đồng</h3>
                <p style="color: var(--muted-light); line-height: 1.6;">
                    Chúng tôi hỗ trợ nông dân địa phương bằng các mối quan hệ đối tác công bằng và minh bạch.
                </p>
            </div>
            
            <div style="background: white; padding: 2rem; border-radius: 1rem; text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.05); transition: transform 0.3s;">
                <div style="width: 80px; height: 80px; margin: 0 auto 1.5rem; border-radius: 50%; background: rgba(182, 230, 51, 0.2); display: flex; align-items: center; justify-content: center;">
                    <span class="material-symbols-outlined" style="font-size: 2.5rem; color: var(--primary-dark);">verified</span>
                </div>
                <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1rem;">Tinh Khiết</h3>
                <p style="color: var(--muted-light); line-height: 1.6;">
                    Cam kết của chúng tôi là 100% sản phẩm hữu cơ được chứng nhận, không hóa chất độc hại.
                </p>
            </div>
            
            <div style="background: white; padding: 2rem; border-radius: 1rem; text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.05); transition: transform 0.3s;">
                <div style="width: 80px; height: 80px; margin: 0 auto 1.5rem; border-radius: 50%; background: rgba(182, 230, 51, 0.2); display: flex; align-items: center; justify-content: center;">
                    <span class="material-symbols-outlined" style="font-size: 2.5rem; color: var(--primary-dark);">visibility</span>
                </div>
                <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1rem;">Minh Bạch</h3>
                <p style="color: var(--muted-light); line-height: 1.6;">
                    Chúng tôi tin rằng bạn có quyền biết thực phẩm của mình đến từ đâu.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Process Section -->
<section style="padding: 4rem 1rem;">
    <div style="max-width: 1280px; margin: 0 auto;">
        <div style="text-align: center; margin-bottom: 3rem;">
            <h2 style="font-size: 2.5rem; font-weight: 700; margin-bottom: 1rem;">Từ Nông Trại Đến Bàn Ăn</h2>
            <p style="font-size: 1.125rem; color: var(--muted-light);">
                Một hành trình của sự chăm sóc, chất lượng và tươi ngon trong từng bước
            </p>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 3rem; position: relative;">
            <!-- Step 1 -->
            <div style="text-align: center; position: relative;">
                <div style="width: 100px; height: 100px; margin: 0 auto 1.5rem; border-radius: 50%; background: var(--primary); display: flex; align-items: center; justify-content: center; box-shadow: 0 10px 25px rgba(182, 230, 51, 0.3);">
                    <span class="material-symbols-outlined" style="font-size: 3rem; color: white;">eco</span>
                </div>
                <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 0.75rem;">1. Tìm Kiếm Tận Tâm</h3>
                <p style="color: var(--muted-light); line-height: 1.6;">
                    Chúng tôi hợp tác với các trang trại hữu cơ được chứng nhận, chia sẻ chung giá trị về nông nghiệp bền vững.
                </p>
            </div>
            
            <!-- Step 2 -->
            <div style="text-align: center; position: relative;">
                <div style="width: 100px; height: 100px; margin: 0 auto 1.5rem; border-radius: 50%; background: var(--primary); display: flex; align-items: center; justify-content: center; box-shadow: 0 10px 25px rgba(182, 230, 51, 0.3);">
                    <span class="material-symbols-outlined" style="font-size: 3rem; color: white;">grass</span>
                </div>
                <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 0.75rem;">2. Canh Tác Tự Nhiên</h3>
                <p style="color: var(--muted-light); line-height: 1.6;">
                    Sản phẩm của chúng tôi được trồng tự nhiên, không hóa chất tổng hợp, để đảm bảo sự tinh khiết.
                </p>
            </div>
            
            <!-- Step 3 -->
            <div style="text-align: center; position: relative;">
                <div style="width: 100px; height: 100px; margin: 0 auto 1.5rem; border-radius: 50%; background: var(--primary); display: flex; align-items: center; justify-content: center; box-shadow: 0 10px 25px rgba(182, 230, 51, 0.3);">
                    <span class="material-symbols-outlined" style="font-size: 3rem; color: white;">local_shipping</span>
                </div>
                <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 0.75rem;">3. Vận Chuyển Cẩn Thận</h3>
                <p style="color: var(--muted-light); line-height: 1.6;">
                    Chúng tôi thu hoạch vào độ chín ngon nhất và giao hàng nhanh chóng để đảm bảo độ tươi.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Team Section -->
<section style="padding: 4rem 1rem; background: rgba(182, 230, 51, 0.05);">
    <div style="max-width: 1280px; margin: 0 auto;">
        <div style="text-align: center; margin-bottom: 3rem;">
            <h2 style="font-size: 2.5rem; font-weight: 700; margin-bottom: 1rem;">Gặp Gỡ Những Người Nông Dân</h2>
            <p style="font-size: 1.125rem; color: var(--muted-light);">
                Những đôi tay tận tụy và những gương mặt tươi cười đằng sau thực phẩm tươi ngon của bạn
            </p>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem;">
            <div style="text-align: center;">
                <div style="width: 180px; height: 180px; margin: 0 auto 1rem; border-radius: 50%; overflow: hidden; border: 4px solid var(--primary);">
                    <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuCORBZn9fAR9lxoVWZ_1rt4JZ3BabN-gnQpT3UfkTi8s_QD97sVrCfIHSI10hjorMVYyMYcbEracplCvVuUyNcLoa0-cD0LYYoDLU5vn8J1Y6dbE2xyAQCa_wbFtdJvNyD5hA91MM76aclU8drvUAYYaACnk_2RnInavQqejyplw9ln9VWoFNpIlADUzhPOV0twAg-8ixLFDTQ_ZgH3i1sK1C4QMdBdvRsxUQX3f-Vqio2ZjKQIcqmqSY-xrrFpTys0wNABqishLLuB" 
                         alt="Chị An" style="width: 100%; height: 100%; object-fit: cover;">
                </div>
                <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 0.5rem;">Chị An</h3>
                <p style="color: var(--muted-light);">Nông dân chính</p>
            </div>
            
            <div style="text-align: center;">
                <div style="width: 180px; height: 180px; margin: 0 auto 1rem; border-radius: 50%; overflow: hidden; border: 4px solid var(--primary);">
                    <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuDOS99iSJCsGaLtsp2cTnYr1PgsS4f6yf2dh5OtJx6c76hadnFH7FraKVwF2hTin4PseEKY5wHNomTxSTulh1J_Tt5YGBMXRNNDK5oySY0JCN0zM1gOWXjwZBNlGGLXcXCNl7Rqto6mhqqwDFzsX3LxOvuRsPEqVdWutm7kjZk52FMMcWlz2Wc_CV24otFIbD1_LHnrInw_wQqJK808vwhkzXiUcT7RFnDxLZYbF6JyuxrWumfDmAG3fGs0j9NS6DNAkSp1pRAGx1jw" 
                         alt="Anh Bình" style="width: 100%; height: 100%; object-fit: cover;">
                </div>
                <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 0.5rem;">Anh Bình</h3>
                <p style="color: var(--muted-light);">Đảm bảo chất lượng</p>
            </div>
            
            <div style="text-align: center;">
                <div style="width: 180px; height: 180px; margin: 0 auto 1rem; border-radius: 50%; overflow: hidden; border: 4px solid var(--primary);">
                    <img src="https://lh3.googleusercontent.com/aida-public/AB6AXuCVWBtAAXz_MHFMzXpn_hL-zvY2OO0MuxsmMvlzM-0q_pFKgWeutioN__AGyk9FYYwrW--4un68KrRmhgxyStSkk97ooIszU8eLgzOOT6pAr5l31M3kZFjjCmTXAkfhS_jKeuCjp_NEKJgVgAC04EKWj9L2iYd7QXNp4oLulaDQtChnDO3kRaezsEfHAqCE4Q-MDGcEwFYDXXZ8AX4x0HpUTpzZSdsU_cqEwye5buJa2SxMe6vvIbo_cNsNasYK-NQTLtGzJgVrH9LC" 
                         alt="Cô Cúc" style="width: 100%; height: 100%; object-fit: cover;">
                </div>
                <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 0.5rem;">Cô Cúc</h3>
                <p style="color: var(--muted-light);">Đối tác cộng đồng</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section style="padding: 4rem 1rem;">
    <div style="max-width: 800px; margin: 0 auto; text-align: center; background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); padding: 4rem 2rem; border-radius: 1rem;">
        <h2 style="font-size: 2.5rem; font-weight: 700; color: white; margin-bottom: 1rem;">
            Gia Nhập Gia Đình Xanh Organic
        </h2>
        <p style="font-size: 1.125rem; color: white; opacity: 0.95; margin-bottom: 2rem;">
            Trải nghiệm hương vị của thực phẩm thật, lành mạnh. Khám phá các sản phẩm theo mùa của chúng tôi và mang sự tốt lành từ nông trại đến bàn ăn của bạn ngay hôm nay.
        </p>
        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
            <a href="<?= SITE_URL ?>/products.php" class="btn" style="background: white; color: var(--primary-dark); padding: 1rem 2rem; font-size: 1.125rem;">
                Mua Sản Phẩm
            </a>
            <a href="<?= SITE_URL ?>/contact.php" class="btn" style="background: transparent; color: white; border: 2px solid white; padding: 1rem 2rem; font-size: 1.125rem;">
                Liên Hệ Với Chúng Tôi
            </a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
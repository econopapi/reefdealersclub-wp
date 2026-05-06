/**
 * RDC Custom Header JavaScript
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        const menuToggle = $('.rdc-menu-toggle');
        const sidebarMenu = $('.rdc-sidebar-menu');
        const sidebarOverlay = $('.rdc-sidebar-overlay');
        const sidebarClose = $('.sidebar-close');
        const submenuClose = $('.submenu-sidebar-close');
        const submenuPanel = $('.rdc-submenu-panel');
        const submenuContent = $('.submenu-panel-content');
        const searchWrap = $('.rdc-search');
        const searchForm = $('.rdc-search .search-form');
        const searchField = searchForm.find('.search-field');
        const searchClear = searchForm.find('.search-clear');
        const searchSubmit = searchForm.find('.search-submit');

        function escapeHtml(value) {
            return $('<div>').text(value == null ? '' : String(value)).html();
        }

        function buildViewAllLink(title, href) {
            if (!href) {
                return '';
            }

            const safeTitle = escapeHtml((title || '').trim());
            const safeHref = escapeHtml(href);

            return '<div class="submenu-view-all"><a href="' + safeHref + '">Ver todo de ' + safeTitle + '</a></div>';
        }

        function enhanceQuickLinkEmojis() {
            $('.sidebar-quick-links .quick-links-list a').each(function() {
                const link = this;
                const $link = $(link);

                if ($link.find('.quick-link-emoji').length) {
                    return;
                }

                const firstNode = link.firstChild;
                if (!firstNode || firstNode.nodeType !== Node.TEXT_NODE) {
                    return;
                }

                const rawText = firstNode.nodeValue || '';
                const trimmedText = rawText.replace(/^\s+/, '');
                const leadingWhitespace = rawText.slice(0, rawText.length - trimmedText.length);
                const firstSpace = trimmedText.indexOf(' ');

                if (firstSpace <= 0) {
                    return;
                }

                const emoji = trimmedText.slice(0, firstSpace);
                const remainingText = trimmedText.slice(firstSpace + 1);

                if (!/[^\u0000-\u007F]/.test(emoji)) {
                    return;
                }

                const emojiSpan = document.createElement('span');
                emojiSpan.className = 'quick-link-emoji';
                emojiSpan.textContent = emoji;

                const beforeText = document.createTextNode(leadingWhitespace);
                const afterText = document.createTextNode(' ' + remainingText);

                link.replaceChild(afterText, firstNode);
                link.insertBefore(emojiSpan, afterText);
                link.insertBefore(beforeText, emojiSpan);
            });
        }

        function isMobileSearch() {
            return window.matchMedia('(max-width: 768px)').matches;
        }

        function openMobileSearch() {
            if (!isMobileSearch() || !searchWrap.length) {
                return;
            }

            searchWrap.addClass('is-expanded');

            setTimeout(function() {
                if (searchField.length) {
                    searchField.trigger('focus');
                }
            }, 30);
        }

        function updateSearchClearState() {
            if (!searchClear.length || !searchField.length) {
                return;
            }

            const hasValue = $.trim(searchField.val()).length > 0;
            searchClear.prop('hidden', !hasValue);
        }

        function closeMobileSearch() {
            if (!searchWrap.length) {
                return;
            }

            if ($.trim(searchField.val()).length > 0) {
                return;
            }

            searchWrap.removeClass('is-expanded');
        }

        enhanceQuickLinkEmojis();
        updateSearchClearState();

        searchField.on('input change', function() {
            updateSearchClearState();
        });

        searchClear.on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            searchField.val('');
            updateSearchClearState();
            searchField.trigger('focus');
            if (isMobileSearch()) {
                closeMobileSearch();
            }
        });

        searchSubmit.on('click', function(e) {
            if (!isMobileSearch()) {
                return;
            }

            if (!searchWrap.hasClass('is-expanded')) {
                e.preventDefault();
                openMobileSearch();
                return;
            }

            const hasValue = $.trim(searchField.val()).length > 0;
            const isFocused = document.activeElement === searchField.get(0);

            if (!hasValue || !isFocused) {
                e.preventDefault();
                searchField.trigger('focus');
            }
        });

        searchField.on('focus touchstart', function() {
            if (isMobileSearch() && !searchWrap.hasClass('is-expanded')) {
                openMobileSearch();
            }
        });

        searchForm.on('submit', function(e) {
            if (!isMobileSearch()) {
                return;
            }

            if ($.trim(searchField.val()).length === 0) {
                e.preventDefault();
                searchField.trigger('focus');
            }

            updateSearchClearState();
        });

        $(document).on('click touchstart', function(e) {
            if (!isMobileSearch()) {
                return;
            }

            if (!$(e.target).closest('.rdc-search').length) {
                closeMobileSearch();
            }
        });

        $(window).on('resize', function() {
            if (!isMobileSearch()) {
                searchWrap.removeClass('is-expanded');
                return;
            }

            if ($.trim(searchField.val()).length > 0) {
                searchWrap.addClass('is-expanded');
            }
        });

        // Open sidebar
        menuToggle.on('click', function() {
            sidebarMenu.addClass('active');
            sidebarOverlay.addClass('active');
            // Use simple overflow lock to avoid interfering with inner scrolling.
            $('body').css('overflow', 'hidden');
        });

        // Cerrar sidebar
        function closeSidebar() {
            sidebarMenu.removeClass('active');
            sidebarOverlay.removeClass('active');
            submenuPanel.removeClass('active');
            $('.sidebar-menu-list .menu-item').removeClass('active');

            // Restore body scroll behavior.
            $('body').css('overflow', '');
            
            // Pequeño delay para que la animación se vea mejor
            setTimeout(function() {
                submenuContent.html('');
            }, 300);
        }

        // Cerrar submenu sidebar
        function closeSubmenu() {
            submenuPanel.removeClass('active');
        }

        sidebarClose.on('click', closeSidebar);
        sidebarOverlay.on('click', closeSidebar);
        submenuClose.on('click', closeSubmenu);

        // Cerrar sidebar con ESC
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && sidebarMenu.hasClass('active')) {
                closeSidebar();
            }
        });

        // Handle click on sidebar categories
        $('.sidebar-menu-list').on('click', '.menu-item > a', function(e) {
            const $link = $(this);
            const $menuItem = $link.parent();
            const catId = $link.data('cat-id');
            const href = $link.attr('href');
            const $subMenu = $menuItem.children('.sub-menu');
            const hasSubMenu = $subMenu.length > 0;

            // Si no es categoría ni tiene hijos, comportamiento normal
            if (!catId && !hasSubMenu) return;

            e.preventDefault();

            // Toggle activo
            if ($menuItem.hasClass('active')) {
                $menuItem.removeClass('active');
                $('.sidebar-menu-list .menu-item').removeClass('active');
                $('.rdc-submenu-panel').removeClass('active');
                return;
            }
            $('.sidebar-menu-list .menu-item').removeClass('active');
            $menuItem.addClass('active');

            const title = ($link.text() || '').trim();

            // Caso 1: el usuario creó hijos en Menús (curado manualmente)
            if (hasSubMenu) {
                const $children = $subMenu.children('li.menu-item');

                // Si no hay hijos reales, navegar a la categoría
                if (!$children.length) {
                    window.location.href = href;
                    return;
                }

                // Construir panel con hijos
                let html = '<h4>' + escapeHtml(title) + '</h4>';
                html += buildViewAllLink(title, href);
                $children.each(function() {
                    const $child = $(this);
                    const $a = $child.children('a');
                    const childTitle = ($a.text() || '').trim();
                    const $grand = $child.children('.sub-menu');
                    const childHref = $a.attr('href') || '#';

                    if ($grand.length) {
                        html += '<div class="submenu-group"><h5>' + escapeHtml(childTitle) + '</h5><ul>';
                        $grand.children('li.menu-item').each(function() {
                            const $ga = $(this).children('a');
                            const grandHref = $ga.attr('href') || '#';
                            const grandTitle = ($ga.text() || '').trim();
                            html += '<li><a href="' + escapeHtml(grandHref) + '">' + escapeHtml(grandTitle) + '</a></li>';
                        });
                        html += '</ul></div>';
                    } else {
                        html += '<ul><li><a href="' + escapeHtml(childHref) + '">' + escapeHtml(childTitle) + '</a></li></ul>';
                    }
                });

                $('.submenu-panel-content').html(html);
                $('.rdc-submenu-panel').addClass('active');
                return;
            }

            // Caso 2: sin hijos en Menús -> consultar por AJAX y abrir solo si hay subcategorías
            if (typeof rdcHeader === 'undefined') {
                // Fallback: navegar si no hay AJAX disponible
                window.location.href = href;
                return;
            }

            $.ajax({
                url: rdcHeader.ajaxUrl,
                method: 'POST',
                data: {
                    action: 'rdc_get_subcategories',
                    nonce: rdcHeader.nonce,
                    catId: catId
                }
            }).done(function(resp) {
                // En error o sin datos, navegar
                if (!resp || !resp.success || !resp.data) {
                    window.location.href = href;
                    return;
                }

                const data = resp.data;
                const groups = Array.isArray(data.groups) ? data.groups : [];
                const hasItems = groups.some(function(g) {
                    return Array.isArray(g.items) && g.items.length > 0;
                });

                // Si no hay subcategorías, navegar
                if (!hasItems) {
                    window.location.href = href;
                    return;
                }

                // Construir y abrir el panel
                let out = '<h4>' + escapeHtml(data.title || title) + '</h4>';
                out += buildViewAllLink(data.title || title, href);
                groups.forEach(function(group) {
                    const hasGroupTitle = group.title && group.title.length;
                    out += '<div class="submenu-group">';
                    if (hasGroupTitle) out += '<h5>' + escapeHtml(group.title) + '</h5>';
                    out += '<ul>';
                    (group.items || []).forEach(function(item) {
                        out += '<li><a href="' + escapeHtml(item.link || '#') + '">' + escapeHtml(item.title || '') + '</a></li>';
                    });
                    out += '</ul></div>';
                });

                $('.submenu-panel-content').html(out);
                $('.rdc-submenu-panel').addClass('active');
            }).fail(function() {
                // Fallback ante fallo de red
                window.location.href = href;
            });
        });

        // Cerrar panel de submenú si clickean afuera
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.sidebar-menu-list, .rdc-submenu-panel').length) {
                if (submenuPanel.hasClass('active')) {
                    submenuPanel.removeClass('active');
                    $('.sidebar-menu-list .menu-item').removeClass('active');
                }
            }
        });
    });
})(jQuery);
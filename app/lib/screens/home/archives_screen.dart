import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:table_calendar/table_calendar.dart';
import 'package:intl/intl.dart';
import '../../models/edition.dart';
import '../../providers/auth_provider.dart';
import '../../providers/edition_provider.dart';
import '../../theme/kn_theme.dart';

class ArchivesScreen extends ConsumerStatefulWidget {
  final String? filterType;
  final String? category;
  final String? title;

  const ArchivesScreen({
    super.key,
    this.filterType,
    this.category,
    this.title,
  });

  @override
  ConsumerState<ArchivesScreen> createState() => _ArchivesScreenState();
}

class _ArchivesScreenState extends ConsumerState<ArchivesScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;

  // Calendar state
  DateTime _focusedMonth = DateTime.now();
  DateTime? _selectedDay;

  // Grid state
  String _typeFilter = 'all';
  String? _categoryFilter;

  @override
  void initState() {
    super.initState();
    final isDailyFirst =
        widget.filterType == null || widget.filterType == 'daily';
    _tabController = TabController(
      length: 2,
      vsync: this,
      initialIndex: isDailyFirst ? 0 : 1,
    );
    if (widget.filterType != null && widget.filterType != 'daily') {
      _typeFilter = widget.filterType!;
    }
    _categoryFilter = widget.category;
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  String get _country => ref.read(authProvider).user?.country ?? 'ug';

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF5F6FA),
      appBar: AppBar(
        title: Text(widget.title ?? 'Archives'),
        backgroundColor: KnColors.navy,
        foregroundColor: Colors.white,
        elevation: 0,
        bottom: TabBar(
          controller: _tabController,
          indicatorColor: KnColors.orange,
          labelColor: Colors.white,
          unselectedLabelColor: Colors.white54,
          labelStyle:
              const TextStyle(fontWeight: FontWeight.w700, fontSize: 14),
          tabs: const [
            Tab(icon: Icon(Icons.calendar_month, size: 18), text: 'Daily'),
            Tab(icon: Icon(Icons.library_books, size: 18), text: 'Special'),
          ],
        ),
      ),
      body: TabBarView(
        controller: _tabController,
        children: [
          _DailyCalendarTab(
            country: _country,
            focusedMonth: _focusedMonth,
            selectedDay: _selectedDay,
            onMonthChanged: (m) => setState(() => _focusedMonth = m),
            onDaySelected: (d) => setState(() => _selectedDay = d),
          ),
          _SpecialEditionsTab(
            country: _country,
            typeFilter: _typeFilter,
            categoryFilter: _categoryFilter,
          ),
        ],
      ),
    );
  }
}

// =============================================================================
// TAB 1: Daily Archives - Calendar
// =============================================================================

class _DailyCalendarTab extends ConsumerWidget {
  final String country;
  final DateTime focusedMonth;
  final DateTime? selectedDay;
  final ValueChanged<DateTime> onMonthChanged;
  final ValueChanged<DateTime> onDaySelected;

  const _DailyCalendarTab({
    required this.country,
    required this.focusedMonth,
    required this.selectedDay,
    required this.onMonthChanged,
    required this.onDaySelected,
  });

  String get _monthKey => DateFormat('yyyy-MM').format(focusedMonth);

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final datesAsync = ref.watch(availableDatesProvider({
      'country': country,
      'month': _monthKey,
    }));

    return SingleChildScrollView(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Container(
            color: KnColors.navy,
            child: datesAsync.when(
              loading: () => _buildCalendar(context, {}),
              error: (_, __) => _buildCalendar(context, {}),
              data: (dates) => _buildCalendar(context, dates),
            ),
          ),
          if (selectedDay != null)
            _SelectedEditionPanel(
              country: country,
              date: DateFormat('yyyy-MM-dd').format(selectedDay!),
            )
          else
            _CalendarHintCard(),
        ],
      ),
    );
  }

  Widget _buildCalendar(BuildContext context, Set<DateTime> availableDates) {
    final now = DateTime.now();

    return TableCalendar<DateTime>(
      firstDay: DateTime(2023, 1, 1),
      lastDay: DateTime(now.year, now.month, now.day),
      focusedDay: focusedMonth,
      selectedDayPredicate: (d) =>
          selectedDay != null && isSameDay(d, selectedDay!),
      calendarFormat: CalendarFormat.month,
      availableCalendarFormats: const {CalendarFormat.month: 'Month'},
      startingDayOfWeek: StartingDayOfWeek.monday,
      eventLoader: (day) {
        final d = DateTime(day.year, day.month, day.day);
        return availableDates.contains(d) ? [d] : [];
      },
      onDaySelected: (selected, focused) {
        onDaySelected(selected);
        if (focused.month != focusedMonth.month) onMonthChanged(focused);
      },
      onPageChanged: onMonthChanged,
      calendarStyle: CalendarStyle(
        outsideDaysVisible: false,
        defaultTextStyle:
            const TextStyle(color: Colors.white, fontSize: 14),
        weekendTextStyle:
            const TextStyle(color: Colors.white70, fontSize: 14),
        selectedDecoration: const BoxDecoration(
          color: KnColors.orange,
          shape: BoxShape.circle,
        ),
        selectedTextStyle: const TextStyle(
            color: Colors.white, fontWeight: FontWeight.w800),
        todayDecoration: BoxDecoration(
          color: Colors.white.withAlpha(40),
          shape: BoxShape.circle,
        ),
        todayTextStyle: const TextStyle(
            color: Colors.white, fontWeight: FontWeight.w700),
        markerDecoration: const BoxDecoration(
          color: KnColors.orange,
          shape: BoxShape.circle,
        ),
        markersMaxCount: 1,
        markerSize: 5,
        markerMargin: const EdgeInsets.only(top: 2),
        disabledTextStyle: const TextStyle(color: Colors.white24),
      ),
      headerStyle: const HeaderStyle(
        formatButtonVisible: false,
        titleCentered: true,
        titleTextStyle: TextStyle(
          color: Colors.white,
          fontSize: 17,
          fontWeight: FontWeight.w700,
        ),
        leftChevronIcon: Icon(Icons.chevron_left, color: Colors.white),
        rightChevronIcon: Icon(Icons.chevron_right, color: Colors.white),
        headerPadding: EdgeInsets.symmetric(vertical: 12),
        decoration: BoxDecoration(color: Colors.transparent),
      ),
      daysOfWeekStyle: const DaysOfWeekStyle(
        weekdayStyle: TextStyle(
            color: Colors.white60, fontSize: 12, fontWeight: FontWeight.w600),
        weekendStyle: TextStyle(
            color: Colors.white38, fontSize: 12, fontWeight: FontWeight.w600),
      ),
      calendarBuilders: CalendarBuilders(
        defaultBuilder: (ctx, day, _) {
          final stripped = DateTime(day.year, day.month, day.day);
          if (availableDates.contains(stripped)) {
            return Container(
              margin: const EdgeInsets.all(4),
              decoration: BoxDecoration(
                color: KnColors.orange.withAlpha(35),
                shape: BoxShape.circle,
              ),
              alignment: Alignment.center,
              child: Text(
                '${day.day}',
                style: const TextStyle(
                    color: Colors.white,
                    fontWeight: FontWeight.w700,
                    fontSize: 13),
              ),
            );
          }
          return null;
        },
      ),
    );
  }
}

class _CalendarHintCard extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.all(20),
      padding: const EdgeInsets.all(28),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
              color: Colors.black.withAlpha(15),
              blurRadius: 12,
              offset: const Offset(0, 4)),
        ],
      ),
      child: Column(
        children: [
          Container(
            width: 64,
            height: 64,
            decoration: BoxDecoration(
                color: KnColors.orange.withAlpha(25), shape: BoxShape.circle),
            child:
                const Icon(Icons.touch_app, color: KnColors.orange, size: 32),
          ),
          const SizedBox(height: 16),
          const Text('Select a Date',
              style: TextStyle(
                  fontSize: 20,
                  fontWeight: FontWeight.w800,
                  color: KnColors.navy)),
          const SizedBox(height: 8),
          const Text(
            'Tap a date on the calendar to view that day\'s edition.\nHighlighted dates have available editions.',
            textAlign: TextAlign.center,
            style: TextStyle(
                fontSize: 14, color: KnColors.textSecondary, height: 1.5),
          ),
          const SizedBox(height: 16),
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Container(
                  width: 10,
                  height: 10,
                  decoration: const BoxDecoration(
                      color: KnColors.orange, shape: BoxShape.circle)),
              const SizedBox(width: 8),
              const Text('= Edition available',
                  style: TextStyle(
                      fontSize: 12,
                      color: KnColors.textMuted,
                      fontWeight: FontWeight.w600)),
            ],
          ),
        ],
      ),
    );
  }
}

class _SelectedEditionPanel extends ConsumerWidget {
  final String country;
  final String date;

  const _SelectedEditionPanel({required this.country, required this.date});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final editionAsync =
        ref.watch(editionByDateProvider({'country': country, 'date': date}));
    final displayDate =
        DateFormat('EEEE, MMMM d, yyyy').format(DateTime.parse(date));

    return Padding(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Row(
            children: [
              const Icon(Icons.calendar_today,
                  size: 15, color: KnColors.orange),
              const SizedBox(width: 8),
              Text(displayDate,
                  style: const TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.w700,
                      color: KnColors.navy)),
            ],
          ),
          const SizedBox(height: 12),
          editionAsync.when(
            loading: () => const Center(
                child: Padding(
                    padding: EdgeInsets.all(40),
                    child: CircularProgressIndicator(color: KnColors.orange))),
            error: (e, _) => const _ErrorCard(
                message: 'Failed to load edition. Check your connection.'),
            data: (edition) => edition == null
                ? _NoEditionCard(date: displayDate)
                : _EditionResultCard(edition: edition),
          ),
        ],
      ),
    );
  }
}

class _NoEditionCard extends StatelessWidget {
  final String date;
  const _NoEditionCard({required this.date});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(28),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
              color: Colors.black.withAlpha(10),
              blurRadius: 8,
              offset: const Offset(0, 2))
        ],
      ),
      child: Column(
        children: [
          const Icon(Icons.newspaper_outlined,
              size: 52, color: KnColors.textMuted),
          const SizedBox(height: 12),
          const Text('No Edition Available',
              style: TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.w700,
                  color: KnColors.navy)),
          const SizedBox(height: 6),
          Text(
            'No edition was published on $date.\nTry a highlighted date.',
            textAlign: TextAlign.center,
            style: const TextStyle(
                fontSize: 13, color: KnColors.textSecondary, height: 1.5),
          ),
        ],
      ),
    );
  }
}

class _EditionResultCard extends StatelessWidget {
  final Edition edition;
  const _EditionResultCard({required this.edition});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: () {
        if (edition.htmlUrl != null) {
          context.push('/reader',
              extra: {'url': edition.htmlUrl, 'title': edition.title});
        }
      },
      child: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          boxShadow: [
            BoxShadow(
                color: Colors.black.withAlpha(12),
                blurRadius: 12,
                offset: const Offset(0, 4))
          ],
        ),
        child: Row(
          children: [
            ClipRRect(
              borderRadius: const BorderRadius.only(
                  topLeft: Radius.circular(16),
                  bottomLeft: Radius.circular(16)),
              child: edition.coverImage != null
                  ? CachedNetworkImage(
                      imageUrl: edition.coverImage!,
                      width: 110,
                      height: 160,
                      fit: BoxFit.cover,
                      placeholder: (_, __) => _ph(),
                      errorWidget: (_, __, ___) => _ph(),
                    )
                  : _ph(),
            ),
            Expanded(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    if (edition.isFree)
                      Container(
                        padding: const EdgeInsets.symmetric(
                            horizontal: 8, vertical: 2),
                        margin: const EdgeInsets.only(bottom: 8),
                        decoration: BoxDecoration(
                            color: KnColors.success.withAlpha(25),
                            borderRadius: BorderRadius.circular(6)),
                        child: const Text('FREE ACCESS',
                            style: TextStyle(
                                fontSize: 10,
                                fontWeight: FontWeight.w800,
                                color: KnColors.success)),
                      ),
                    Text(edition.title,
                        style: const TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.w800,
                            color: KnColors.navy,
                            height: 1.3)),
                    const SizedBox(height: 4),
                    Text(edition.editionDate,
                        style: const TextStyle(
                            fontSize: 12, color: KnColors.textMuted)),
                    if (edition.pageCount > 0) ...[
                      const SizedBox(height: 2),
                      Text('${edition.pageCount} pages',
                          style: const TextStyle(
                              fontSize: 11, color: KnColors.textMuted)),
                    ],
                    const SizedBox(height: 14),
                    SizedBox(
                      width: double.infinity,
                      child: ElevatedButton.icon(
                        onPressed: edition.htmlUrl != null
                            ? () => context.push('/reader', extra: {
                                  'url': edition.htmlUrl,
                                  'title': edition.title
                                })
                            : null,
                        icon: const Icon(Icons.menu_book, size: 15),
                        label: Text(
                          (edition.accessible ?? edition.isFree)
                              ? 'Read Edition'
                              : 'Subscribe to Read',
                          style: const TextStyle(
                              fontSize: 12, fontWeight: FontWeight.w700),
                        ),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: KnColors.orange,
                          foregroundColor: Colors.white,
                          padding: const EdgeInsets.symmetric(vertical: 10),
                          shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(8)),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _ph() => Container(
        width: 110,
        height: 160,
        decoration: BoxDecoration(
          gradient: LinearGradient(
            colors: [KnColors.navy, KnColors.navy.withAlpha(180)],
          ),
        ),
        child: const Center(
            child: Icon(Icons.newspaper, size: 32, color: Colors.white30)),
      );
}

class _ErrorCard extends StatelessWidget {
  final String message;
  const _ErrorCard({required this.message});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
          color: Colors.white, borderRadius: BorderRadius.circular(12)),
      child: Row(
        children: [
          const Icon(Icons.error_outline, color: KnColors.error),
          const SizedBox(width: 12),
          Expanded(
              child: Text(message,
                  style: const TextStyle(color: KnColors.error))),
        ],
      ),
    );
  }
}

// =============================================================================
// TAB 2: Special Editions Grid
// =============================================================================

class _SpecialEditionsTab extends ConsumerStatefulWidget {
  final String country;
  final String typeFilter;
  final String? categoryFilter;

  const _SpecialEditionsTab({
    required this.country,
    required this.typeFilter,
    required this.categoryFilter,
  });

  @override
  ConsumerState<_SpecialEditionsTab> createState() =>
      _SpecialEditionsTabState();
}

class _SpecialEditionsTabState extends ConsumerState<_SpecialEditionsTab> {
  String _type = 'all';
  String? _category;

  @override
  void initState() {
    super.initState();
    _type = widget.typeFilter;
    _category = widget.categoryFilter;
  }

  @override
  Widget build(BuildContext context) {
    final params = <String, dynamic>{
      'country': widget.country,
      'page': 1,
      'per_page': 50,
    };
    if (_type != 'all') params['type'] = _type;
    if (_category != null) params['category'] = _category;

    final editionsAsync = ref.watch(editionsProvider(params));

    return Column(
      children: [
        Container(
          color: Colors.white,
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
          child: SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            child: Row(
              children: [
                _Chip(
                    label: 'All',
                    selected: _type == 'all',
                    onTap: () =>
                        setState(() {
                          _type = 'all';
                          _category = null;
                        })),
                _Chip(
                    label: 'Special',
                    selected: _type == 'special',
                    onTap: () =>
                        setState(() {
                          _type = 'special';
                          _category = null;
                        })),
                _Chip(
                    label: 'Rate Card',
                    selected: _type == 'rate_card',
                    onTap: () =>
                        setState(() {
                          _type = 'rate_card';
                          _category = null;
                        })),
              ],
            ),
          ),
        ),
        Expanded(
          child: editionsAsync.when(
            loading: () => const Center(
                child: CircularProgressIndicator(color: KnColors.orange)),
            error: (e, _) => const Center(child: Text('Error loading editions')),
            data: (data) {
              final editions = data['editions'] as List<Edition>;
              if (editions.isEmpty) {
                return const Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(Icons.library_books_outlined,
                          size: 64, color: KnColors.textMuted),
                      SizedBox(height: 16),
                      Text('No editions found',
                          style: TextStyle(
                              fontSize: 18,
                              fontWeight: FontWeight.w600,
                              color: KnColors.textSecondary)),
                    ],
                  ),
                );
              }
              return GridView.builder(
                padding: const EdgeInsets.all(16),
                gridDelegate:
                    const SliverGridDelegateWithFixedCrossAxisCount(
                  crossAxisCount: 2,
                  childAspectRatio: 0.68,
                  mainAxisSpacing: 16,
                  crossAxisSpacing: 16,
                ),
                itemCount: editions.length,
                itemBuilder: (ctx, i) => _GridCard(edition: editions[i]),
              );
            },
          ),
        ),
      ],
    );
  }
}

class _Chip extends StatelessWidget {
  final String label;
  final bool selected;
  final VoidCallback onTap;

  const _Chip(
      {required this.label, required this.selected, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(right: 8),
      child: FilterChip(
        label: Text(label),
        selected: selected,
        onSelected: (_) => onTap(),
        selectedColor: KnColors.orange.withAlpha(50),
        checkmarkColor: KnColors.orange,
        labelStyle: TextStyle(
          fontWeight: FontWeight.w600,
          color: selected ? KnColors.orange : KnColors.textSecondary,
          fontSize: 13,
        ),
        side: BorderSide(color: selected ? KnColors.orange : KnColors.border),
        shape:
            RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
      ),
    );
  }
}

class _GridCard extends StatelessWidget {
  final Edition edition;
  const _GridCard({required this.edition});

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.white,
      borderRadius: BorderRadius.circular(14),
      elevation: 2,
      child: InkWell(
        borderRadius: BorderRadius.circular(14),
        onTap: () {
          if (edition.htmlUrl != null) {
            context.push('/reader',
                extra: {'url': edition.htmlUrl, 'title': edition.title});
          }
        },
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Expanded(
              flex: 3,
              child: ClipRRect(
                borderRadius:
                    const BorderRadius.vertical(top: Radius.circular(14)),
                child: edition.coverImage != null
                    ? CachedNetworkImage(
                        imageUrl: edition.coverImage!,
                        fit: BoxFit.cover,
                        placeholder: (_, __) => _ph(),
                        errorWidget: (_, __, ___) => _ph(),
                      )
                    : _ph(),
              ),
            ),
            Expanded(
              flex: 2,
              child: Padding(
                padding: const EdgeInsets.all(10),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(
                          horizontal: 6, vertical: 2),
                      decoration: BoxDecoration(
                          color: KnColors.orange.withAlpha(25),
                          borderRadius: BorderRadius.circular(4)),
                      child: Text(edition.typeLabel,
                          style: const TextStyle(
                              fontSize: 9,
                              fontWeight: FontWeight.w700,
                              color: KnColors.orange)),
                    ),
                    const SizedBox(height: 4),
                    Flexible(
                      child: Text(edition.title,
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                          style: const TextStyle(
                              fontWeight: FontWeight.w700,
                              fontSize: 12,
                              color: KnColors.navy)),
                    ),
                    Text(edition.editionDate,
                        style: const TextStyle(
                            fontSize: 10, color: KnColors.textMuted)),
                    if (edition.isFree)
                      const Text('FREE',
                          style: TextStyle(
                              fontSize: 9,
                              fontWeight: FontWeight.w800,
                              color: KnColors.success)),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _ph() => Container(
        decoration: BoxDecoration(
          gradient: LinearGradient(
            colors: [KnColors.navy, KnColors.navy.withAlpha(180)],
          ),
        ),
        child: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Text(edition.typeIcon, style: const TextStyle(fontSize: 28)),
              const SizedBox(height: 6),
              const Text('KandaNews',
                  style: TextStyle(
                      color: Colors.white,
                      fontWeight: FontWeight.w700,
                      fontSize: 10)),
            ],
          ),
        ),
      );
}

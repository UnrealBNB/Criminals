<div class="game-menu">
    <h3>Navigation</h3>
    <ul>
        <li><a href="/game">Dashboard</a></li>
        <li><a href="/game/bank">Bank</a></li>
        <li><a href="/game/shop">Shop</a></li>
        <li><a href="/game/list">User List</a></li>
        <li><a href="/game/messages">Messages</a></li>
        <li><a href="/game/profile">Profile</a></li>
        <li><a href="/game/donate">Donate</a></li>
        <li><a href="/game/flight">Travel</a></li>
        <li><a href="/game/type-change">Change Type</a></li>
    </ul>

    <h3>Gambling</h3>
    <ul>
        <li><a href="/game/gambling/number-game">Number Game</a></li>
        <li><a href="/game/gambling/coin-flip">Coin Flip</a></li>
        <li><a href="/game/gambling/rps">Rock Paper Scissors</a></li>
        <li><a href="/game/gambling/russian-roulette">Russian Roulette</a></li>
        <li><a href="/game/gambling/higher-lower">Higher/Lower</a></li>
        <li><a href="/game/gambling/bank-robbery">Bank Robbery</a></li>
        <li><a href="/game/gambling/horse-race">Horse Race</a></li>
        <li><a href="/game/gambling/roulette">Roulette</a></li>
    </ul>

    <h3>Clan</h3>
    <ul>
        <?php if (auth()->user()->isInClan()): ?>
            <li><a href="/game/clan/overview">Overview</a></li>
            <li><a href="/game/clan/members">Members</a></li>
            <li><a href="/game/clan/bank">Clan Bank</a></li>
            <li><a href="/game/clan/shop">Clan Shop</a></li>
            <?php if (auth()->user()->hasClanLevel(7)): ?>
                <li><a href="/game/clan/message">Send Message</a></li>
            <?php endif; ?>
            <?php if (auth()->user()->hasClanLevel(5)): ?>
                <li><a href="/game/clan/applications">Applications</a></li>
            <?php endif; ?>
        <?php else: ?>
            <li><a href="/game/clan">Join/Create Clan</a></li>
        <?php endif; ?>
        <li><a href="/game/clan/list">All Clans</a></li>
    </ul>
</div>